<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $counts = [];
        $total = 0;

        foreach (TicketStatus::cases() as $status) {
            $count = Ticket::where('status', $status)->count();
            $counts[$status->value] = ['label' => $status->label(), 'total' => $count];
            $total += $count;
        }

        // Backlog: ticket yang belum Closed (formula PRD).
        $backlog = $total - $counts['closed']['total'];

        // ponytail: rata-rata dihitung in-memory; pindah ke SQL aggregate saat volume besar.
        $avgResponseMinutes = round((float) Ticket::whereNotNull('received_at')
            ->get(['created_at', 'received_at'])
            ->avg(fn ($t) => $t->created_at->diffInMinutes($t->received_at)) ?? 0, 1);

        $avgResolutionHours = round((float) Ticket::whereNotNull('resolved_at')
            ->get(['received_at', 'resolved_at'])
            ->avg(fn ($t) => ($t->received_at ?? $t->created_at)?->diffInHours($t->resolved_at) ?? 0) ?? 0, 1);

        return Inertia::render('admin/dashboard', [
            'kpi' => [
                'total' => $total,
                'statuses' => $counts,
                'backlog' => $backlog,
                'avg_response_minutes' => $avgResponseMinutes,
                'avg_resolution_hours' => $avgResolutionHours,
                // Overdue menunggu SLA target [TBD] — tidak dihitung tanpa keputusan bisnis.
                'overdue' => null,
            ],
            'recentTickets' => Ticket::latest()
                ->take(5)
                ->get(['id', 'ticket_number', 'requester_name', 'status', 'priority_id', 'created_at'])
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'ticket_number' => $t->ticket_number,
                    'requester_name' => $t->requester_name,
                    'status' => $t->status->value,
                    'status_label' => $t->status->label(),
                    'created_at' => $t->created_at?->toDateTimeString(),
                ]),
        ]);
    }
}
