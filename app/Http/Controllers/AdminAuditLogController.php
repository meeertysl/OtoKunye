<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('action'), function ($query) use ($request) {
                $query->where('action', $request->string('action'));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'selectedAction' => (string) $request->query('action', ''),
        ]);
    }
}
