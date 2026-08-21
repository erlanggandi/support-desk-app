<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\ProblemType;
use App\Models\Technician;
use App\Models\Ticket;
use App\Services\TicketWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->string('q'));
        $status = $request->string('status')->toString();
        $priorityId = $request->integer('priority_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $tickets = Ticket::query()
            ->with(['priority:id,name', 'department:id,name', 'technician:id,name'])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('ticket_number', 'like', "%{$q}%")
                ->orWhere('requester_name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")))
            ->when($status !== '' && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($priorityId, fn ($query) => $query->where('priority_id', $priorityId))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Ticket $t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'requester_name' => $t->requester_name,
                'description' => Str::limit($t->description, 80),
                'status' => $t->status->value,
                'status_label' => $t->status->label(),
                'priority' => $t->priority?->name,
                'department' => $t->department?->name,
                'technician' => $t->technician?->name,
                'created_at' => $t->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('admin/tickets/index', [
            'tickets' => $tickets,
            'filters' => [
                'q' => $q,
                'status' => $status !== '' ? $status : 'all',
                'priority_id' => $priorityId,
                'department_id' => $departmentId,
            ],
            'statuses' => TicketStatus::labels(),
            'priorities' => Priority::orderBy('ordering')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load(['department:id,name', 'category:id,name', 'problemType:id,name', 'priority:id,name', 'technician:id,name']);

        return Inertia::render('admin/tickets/show', [
            'ticket' => [
                ...$ticket->only([
                    'id', 'ticket_number', 'tracking_code', 'requester_name', 'requester_contact',
                    'description', 'resolution_notes', 'received_at', 'escalated_at',
                    'in_progress_at', 'resolved_at', 'closed_at', 'technician_id',
                    'priority_id', 'category_id', 'problem_type_id', 'department_id',
                ]),
                'status' => $ticket->status->value,
                'status_label' => $ticket->status->label(),
                'next_statuses' => collect(TicketStatus::cases())
                    ->filter(fn (TicketStatus $s) => $ticket->status->canTransitionTo($s))
                    ->map(fn (TicketStatus $s) => ['value' => $s->value, 'label' => $s->label()])
                    ->values(),
                'timeline' => \App\Http\Controllers\PublicPortal\TicketController::timeline($ticket),
                'department' => $ticket->department?->name,
                'category' => $ticket->category?->name,
                'problem_type' => $ticket->problemType?->name,
                'priority' => $ticket->priority?->name,
                'technician' => $ticket->technician?->name,
                'created_at' => $ticket->created_at?->toDateTimeString(),
            ],
            'technicians' => Technician::active()->orderBy('name')->get(['id', 'name']),
            'priorities' => Priority::orderBy('ordering')->get(['id', 'name']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'problemTypes' => ProblemType::orderBy('name')->get(['id', 'category_id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'audits' => AuditLog::where('entity', 'ticket')
                ->where('entity_id', $ticket->id)
                ->latest()
                ->take(20)
                ->get(['id', 'actor', 'action', 'metadata', 'created_at'])
                ->map(fn ($a) => [
                    'id' => $a->id,
                    'actor' => $a->actor,
                    'action' => $a->action,
                    'metadata' => $a->metadata,
                    'created_at' => $a->created_at?->toDateTimeString(),
                ]),
        ]);
    }

    /**
     * FR-013/014: ubah priority, category, problem type, department.
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'priority_id' => ['sometimes', 'integer', Rule::exists('priorities', 'id')],
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'problem_type_id' => ['nullable', 'integer', Rule::exists('problem_types', 'id')],
            'department_id' => ['sometimes', 'integer', Rule::exists('departments', 'id')],
        ]);

        $changes = [];
        foreach (['priority_id', 'category_id', 'problem_type_id', 'department_id'] as $field) {
            if (array_key_exists($field, $data) && $ticket->{$field} != $data[$field]) {
                $changes[$field] = ['from' => $ticket->{$field}, 'to' => $data[$field]];
                $ticket->{$field} = $data[$field];
            }
        }

        if ($changes !== []) {
            $ticket->save();
            AuditLog::record('ticket.updated', 'ticket', $ticket->id, $changes);
        }

        return back();
    }

    /**
     * AC-005..AC-010: Receive / Escalate / Start / Resolve / Close.
     */
    public function transition(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'technician_id' => ['required_if:status,escalated', 'nullable', 'integer'],
            'resolution_notes' => ['required_if:status,resolved', 'nullable', 'string', 'max:5000'],
        ]);

        TicketWorkflow::transition(
            $ticket,
            TicketStatus::from($data['status']),
            isset($data['technician_id']) ? (int) $data['technician_id'] : null,
            $data['resolution_notes'] ?? null,
        );

        return back();
    }
}
