<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/audit-logs/index', [
            'logs' => AuditLog::latest()
                ->paginate(30)
                ->through(fn ($log) => [
                    'id' => $log->id,
                    'actor' => $log->actor,
                    'action' => $log->action,
                    'entity' => $log->entity,
                    'entity_id' => $log->entity_id,
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at?->toDateTimeString(),
                ]),
        ]);
    }
}
