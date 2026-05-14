<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NetgsmSmsService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.netgsm.enabled', false)
            && filled(config('services.netgsm.usercode'))
            && filled(config('services.netgsm.password'))
            && filled(config('services.netgsm.header'));
    }

    public function send(string $phone, string $message): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Netgsm ayarları eksik. services.netgsm alanlarını kontrol edin.');
        }

        $normalizedPhone = $this->normalizePhone($phone);

        $params = [
            'usercode' => config('services.netgsm.usercode'),
            'password' => config('services.netgsm.password'),
            'gsmno' => $normalizedPhone,
            'message' => $message,
            'msgheader' => config('services.netgsm.header'),
            'dil' => config('services.netgsm.language', 'TR'),
        ];

        $response = Http::asForm()
            ->timeout((int) config('services.netgsm.timeout', 15))
            ->post((string) config('services.netgsm.url'), $params);

        $body = trim((string) $response->body());
        if (! $response->successful() || ! preg_match('/^00\b/', $body)) {
            throw new RuntimeException('Netgsm SMS hatası: ' . ($body !== '' ? $body : 'Yanıt alınamadı'));
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '90')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            throw new RuntimeException('Geçersiz telefon numarası: ' . $phone);
        }

        return $digits;
    }
}
