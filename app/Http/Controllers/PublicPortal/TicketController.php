<?php

namespace App\Http\Controllers\PublicPortal;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\ProblemType;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function create(Request $request): Response
    {
        $formToken = Str::random(40);
        $request->session()->put('ticket_form_token', $formToken);

        return Inertia::render('public/tickets/create', [
            'departments' => Department::active()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'problemTypes' => ProblemType::active()->orderBy('name')->get(['id', 'category_id', 'name']),
            'priorities' => Priority::active()->orderBy('ordering')->get(['id', 'name']),
            'formToken' => $formToken,
        ]);
    }

    /**
     * AC-001/AC-002: buat ticket tanpa login, validasi field required,
     * cegah double submit (one-time form token) dan master data inactive.
     */
    public function store(Request $request): RedirectResponse
    {
        $expected = (string) $request->session()->pull('ticket_form_token');

        if ($expected === '' || ! hash_equals($expected, (string) $request->input('form_token'))) {
            return redirect()
                ->route('tickets.create')
                ->withErrors(['form_token' => 'Sesi formulir sudah kedaluwarsa. Silakan isi ulang formulir.']);
        }

        $data = $request->validate([
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_contact' => ['nullable', 'string', 'max:255'],
            'department_id' => ['required', 'integer', $this->ruleActive(Department::class)],
            'category_id' => ['required', 'integer', $this->ruleActive(Category::class)],
            'problem_type_id' => ['nullable', 'integer', $this->ruleActive(ProblemType::class)],
            'priority_id' => ['required', 'integer', $this->ruleActive(Priority::class)],
            'description' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = DB::transaction(function () use ($data): Ticket {
            $ticket = Ticket::create($data);

            AuditLog::record('ticket.created', 'ticket', $ticket->id, [
                'priority_id' => $ticket->priority_id,
                'department_id' => $ticket->department_id,
                'category_id' => $ticket->category_id,
            ]);

            return $ticket;
        });

        Session::flash('created_ticket', [
            'ticket_number' => $ticket->ticket_number,
            'tracking_code' => $ticket->tracking_code,
        ]);

        return redirect()->route('tickets.success');
    }

    public function success(): Response|RedirectResponse
    {
        $created = session('created_ticket');

        if (! is_array($created)) {
            return redirect()->route('tickets.track');
        }

        return Inertia::render('public/tickets/success', ['createdTicket' => $created]);
    }

    public function track(): Response
    {
        return Inertia::render('public/tickets/track');
    }

    /**
     * AC-003/AC-004: tracking hanya dengan kredensial valid;
     * kredensial salah tidak membocorkan keberadaan ticket.
     */
    public function trackResult(Request $request): Response|RedirectResponse
    {
        $data = $request->validate([
            'ticket_number' => ['required', 'string', 'max:255'],
            'tracking_code' => ['required', 'string', 'max:255'],
        ]);

        $ticket = Ticket::where('ticket_number', trim($data['ticket_number']))
            ->where('tracking_code', trim($data['tracking_code']))
            ->first();

        if ($ticket === null) {
            return redirect()
                ->route('tickets.track')
                ->withInput()
                ->withErrors(['tracking' => 'Ticket tidak ditemukan. Periksa kembali Ticket Number dan Tracking Code Anda.']);
        }

        return Inertia::render('public/tickets/track', [
            'ticket' => [
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status->value,
                'status_label' => $ticket->status->label(),
                'requester_name' => $ticket->requester_name,
                'description' => $ticket->description,
                'created_at' => $ticket->created_at?->toDateTimeString(),
                'technician_name' => $ticket->technician?->name,
                'resolution_notes' => $ticket->resolution_notes,
                'timeline' => self::timeline($ticket),
            ],
        ]);
    }

    /**
     * @return list<array{label: string, at: ?string}>
     */
    public static function timeline(Ticket $ticket): array
    {
        return collect(TicketStatus::cases())
            ->map(fn (TicketStatus $status): array => match ($status) {
                TicketStatus::Open => ['label' => $status->label(), 'at' => $ticket->created_at?->toDateTimeString()],
                TicketStatus::Received => ['label' => $status->label(), 'at' => $ticket->received_at?->toDateTimeString()],
                TicketStatus::Escalated => ['label' => $status->label(), 'at' => $ticket->escalated_at?->toDateTimeString()],
                TicketStatus::InProgress => ['label' => $status->label(), 'at' => $ticket->in_progress_at?->toDateTimeString()],
                TicketStatus::Resolved => ['label' => $status->label(), 'at' => $ticket->resolved_at?->toDateTimeString()],
                TicketStatus::Closed => ['label' => $status->label(), 'at' => $ticket->closed_at?->toDateTimeString()],
            })
            ->values()
            ->all();
    }

    private function ruleActive(string $model): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($model): void {
            if ($value !== null && ! $model::query()->where('id', $value)->where('status', 'active')->exists()) {
                $fail('Data yang dipilih sudah tidak aktif.');
            }
        };
    }
}
