<?php

namespace App\Services;

use App\Models\ServiceRecord;
use RuntimeException;

class ParasutInvoiceService
{
    public function isConfigured(): bool
    {
        return (bool) config('services.parasut.enabled', false)
            && filled(config('services.parasut.api_key'))
            && filled(config('services.parasut.company_id'));
    }

    public function sync(ServiceRecord $serviceRecord): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Paraşüt API ayarları eksik veya pasif.');
        }

        // TODO: Paraşüt API bağlantısı hazır olduğunda gerçek fatura eşlemesi burada yapılacak.
        return [
            'status' => 'hazırlanıyor',
            'external_id' => null,
            'invoice_url' => null,
        ];
    }
}
