<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(
        string $action,
        string $targetType,
        ?int $targetId,
        string $summary,
        ?array $beforeData = null,
        ?array $afterData = null,
        ?array $contextData = null
    ): void {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'summary' => $summary,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'context_data' => $contextData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
