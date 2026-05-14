<?php

namespace App\Jobs;

use App\Models\ServiceRecord;
use App\Services\ParasutInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncParasutInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $serviceRecordId)
    {
    }

    public function handle(ParasutInvoiceService $parasutInvoiceService): void
    {
        $serviceRecord = ServiceRecord::query()->find($this->serviceRecordId);
        if (! $serviceRecord) {
            return;
        }

        try {
            $result = $parasutInvoiceService->sync($serviceRecord);

            $serviceRecord->update([
                'invoice_status' => $result['status'] ?? 'hazırlanıyor',
                'invoice_external_id' => $result['external_id'] ?? null,
                'invoice_url' => $result['invoice_url'] ?? $serviceRecord->invoice_url,
                'invoice_synced_at' => now(),
                'invoice_last_error' => null,
            ]);
        } catch (\Throwable $exception) {
            $serviceRecord->update([
                'invoice_status' => 'hata',
                'invoice_last_error' => $exception->getMessage(),
            ]);

            Log::error('Paraşüt fatura senkronu başarısız.', [
                'service_record_id' => $serviceRecord->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
