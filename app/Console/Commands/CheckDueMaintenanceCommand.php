<?php

namespace App\Console\Commands;

use App\Mail\MaintenanceReminderMail;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NetgsmSmsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckDueMaintenanceCommand extends Command
{
    protected $signature = 'maintenance:check-due {--vehicle_id= : Sadece belirtilen araç ID için hatırlatma gönder}';

    protected $description = 'Yaklaşan bakım tarihlerini kontrol eder, log ve e-posta bildirimi gönderir';
    private bool $smsDisabledLogged = false;

    public function __construct(private readonly NetgsmSmsService $smsService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $notifyUntil = (clone $today)->addDays(3);
        $processedKeys = [];

        $records = ServiceRecord::query()
            ->with('vehicle')
            ->where(function ($query) use ($today, $notifyUntil) {
                $query
                    ->where(function ($dateQuery) use ($today, $notifyUntil) {
                        $dateQuery
                            ->whereNotNull('next_service_date')
                            ->whereBetween('next_service_date', [$today, $notifyUntil]);
                    })
                    ->orWhereNotNull('next_service_km');
            })
            ->when($this->option('vehicle_id'), function ($query) {
                $query->where('vehicle_id', (int) $this->option('vehicle_id'));
            })
            ->get();

        foreach ($records as $record) {
            $vehicle = $record->vehicle;
            if (! $vehicle) {
                continue;
            }

            $isDateReminderDue = $record->next_service_date
                && $record->next_service_date->greaterThanOrEqualTo($today)
                && $record->next_service_date->lessThanOrEqualTo($notifyUntil)
                && ! $record->reminder_sent_for_date?->isSameDay($record->next_service_date);

            $remainingKm = null;
            $isKmReminderDue = false;
            if ($record->next_service_km !== null && $vehicle->current_km !== null) {
                $remainingKm = (int) $record->next_service_km - (int) $vehicle->current_km;
                $isKmReminderDue = $remainingKm >= 0
                    && $remainingKm <= 1000
                    && (int) ($record->reminder_sent_for_km ?? -1) !== (int) $record->next_service_km;
            }

            if (! $isDateReminderDue && ! $isKmReminderDue) {
                continue;
            }

            $reminderKey = $record->vehicle_id . '|'
                . ($isDateReminderDue ? optional($record->next_service_date)->toDateString() : 'date-yok')
                . '|'
                . ($isKmReminderDue ? (string) $record->next_service_km : 'km-yok');

            if (isset($processedKeys[$reminderKey])) {
                continue;
            }

            Log::info("Bakım hatırlatma tetiklendi: {$vehicle->license_plate}");
            $emailSent = $this->sendReminderEmails($record, $isDateReminderDue, $isKmReminderDue, $remainingKm);
            $smsSent = $this->sendReminderSms($record, $isDateReminderDue, $isKmReminderDue, $remainingKm);

            if ($emailSent || $smsSent) {
                $updateData = [];
                if ($isDateReminderDue) {
                    $updateData['reminder_sent_for_date'] = $record->next_service_date;
                }
                if ($isKmReminderDue) {
                    $updateData['reminder_sent_for_km'] = $record->next_service_km;
                }

                if ($updateData !== []) {
                    $record->update($updateData);
                }
            }

            $processedKeys[$reminderKey] = true;
        }

        $inspectionVehicles = Vehicle::query()
            ->whereNotNull('inspection_date')
            ->whereBetween('inspection_date', [$today, $notifyUntil])
            ->when($this->option('vehicle_id'), function ($query) {
                $query->whereKey((int) $this->option('vehicle_id'));
            })
            ->get();

        foreach ($inspectionVehicles as $vehicle) {
            if ($vehicle->inspection_reminder_sent_for_date?->isSameDay($vehicle->inspection_date)) {
                continue;
            }

            Log::info("Muayene hatırlatma tetiklendi: {$vehicle->license_plate}");
            $emailSent = $this->sendInspectionReminderEmails($vehicle);
            $smsSent = $this->sendInspectionReminderSms($vehicle);

            if ($emailSent || $smsSent) {
                $vehicle->update([
                    'inspection_reminder_sent_for_date' => $vehicle->inspection_date,
                ]);
            }
        }

        $this->info('Bakım ve muayene tarihi kontrolü, e-posta ve SMS gönderimleri tamamlandı.');

        return 0;
    }

    private function sendReminderEmails(
        ServiceRecord $record,
        bool $isDateReminderDue,
        bool $isKmReminderDue,
        ?int $remainingKm
    ): bool
    {
        $vehicle = $record->vehicle;
        $mailSubject = "Bakım Hatırlatması - {$vehicle->license_plate}";
        $mailBody = $this->buildReminderMessage($record, $isDateReminderDue, $isKmReminderDue, $remainingKm);
        $sent = false;

        if (! empty($vehicle->customer_email)) {
            $sent = $this->sendMailSafely($vehicle->customer_email, $mailSubject, $mailBody) || $sent;
        }

        User::query()
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->each(function (string $email) use ($mailSubject, $mailBody, &$sent) {
                $sent = $this->sendMailSafely($email, $mailSubject, $mailBody) || $sent;
            });

        return $sent;
    }

    private function sendMailSafely(string $to, string $subject, string $body): bool
    {
        try {
            Mail::to($to)->send(new MaintenanceReminderMail($subject, $body));

            Log::info("Bakım hatırlatma e-postası gönderildi: {$to}");
            return true;
        } catch (\Throwable $exception) {
            Log::error("Bakım hatırlatma e-postası gönderilemedi: {$to}", [
                'error' => $exception->getMessage(),
            ]);
            return false;
        }

    }

    private function sendReminderSms(
        ServiceRecord $record,
        bool $isDateReminderDue,
        bool $isKmReminderDue,
        ?int $remainingKm
    ): bool
    {
        $vehicle = $record->vehicle;

        if (! $this->smsService->isConfigured()) {
            if (! $this->smsDisabledLogged) {
                Log::info('SMS gönderimi pasif (NETGSM_ENABLED=false veya Netgsm ayarları eksik). Sadece e-posta gönderimi devam ediyor.');
                $this->smsDisabledLogged = true;
            }
            return false;
        }

        if (empty($vehicle->customer_phone)) {
            Log::warning("Müşteri telefonu bulunamadığı için SMS gönderimi atlandı. Araç: {$vehicle->license_plate}");
            return false;
        }

        $message = $this->buildReminderMessage($record, $isDateReminderDue, $isKmReminderDue, $remainingKm);

        try {
            $this->smsService->send($vehicle->customer_phone, $message);
            Log::info("Bakım hatırlatma SMS'i gönderildi: {$vehicle->customer_phone}");
            return true;
        } catch (\Throwable $exception) {
            Log::error("Bakım hatırlatma SMS'i gönderilemedi: {$vehicle->customer_phone}", [
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function buildReminderMessage(
        ServiceRecord $record,
        bool $isDateReminderDue,
        bool $isKmReminderDue,
        ?int $remainingKm
    ): string {
        $vehicle = $record->vehicle;
        $parts = [];

        if ($isDateReminderDue) {
            $parts[] = "bakım tarihi " . optional($record->next_service_date)->format('d.m.Y');
        }

        if ($isKmReminderDue && $remainingKm !== null) {
            $parts[] = "sonraki bakıma {$remainingKm} km kaldı";
        }

        $reasonText = implode(' ve ', $parts);

        return "{$vehicle->license_plate} plakalı aracınız için {$reasonText}. Randevu için servisimizle iletişime geçin. OTOKÜNYE";
    }

    private function sendInspectionReminderEmails(Vehicle $vehicle): bool
    {
        $subject = "Muayene Hatırlatması - {$vehicle->license_plate}";
        $body = $this->buildInspectionReminderMessage($vehicle);
        $sent = false;

        if (! empty($vehicle->customer_email)) {
            $sent = $this->sendMailSafely($vehicle->customer_email, $subject, $body) || $sent;
        }

        User::query()
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->each(function (string $email) use ($subject, $body, &$sent) {
                $sent = $this->sendMailSafely($email, $subject, $body) || $sent;
            });

        return $sent;
    }

    private function sendInspectionReminderSms(Vehicle $vehicle): bool
    {
        if (! $this->smsService->isConfigured()) {
            if (! $this->smsDisabledLogged) {
                Log::info('SMS gönderimi pasif (NETGSM_ENABLED=false veya Netgsm ayarları eksik). Sadece e-posta gönderimi devam ediyor.');
                $this->smsDisabledLogged = true;
            }
            return false;
        }

        if (empty($vehicle->customer_phone)) {
            Log::warning("Müşteri telefonu bulunamadığı için SMS gönderimi atlandı. Araç: {$vehicle->license_plate}");
            return false;
        }

        try {
            $this->smsService->send($vehicle->customer_phone, $this->buildInspectionReminderMessage($vehicle));
            Log::info("Muayene hatırlatma SMS'i gönderildi: {$vehicle->customer_phone}");
            return true;
        } catch (\Throwable $exception) {
            Log::error("Muayene hatırlatma SMS'i gönderilemedi: {$vehicle->customer_phone}", [
                'error' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    private function buildInspectionReminderMessage(Vehicle $vehicle): string
    {
        $inspectionDate = optional($vehicle->inspection_date)->format('d.m.Y');

        return "{$vehicle->license_plate} plakalı aracınızın muayene tarihi {$inspectionDate}. Süre dolmadan muayene planlaması yapınız. OTOKÜNYE";
    }
}
