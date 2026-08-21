<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\Technician;
use Illuminate\Validation\ValidationException;

class TicketWorkflow
{
    /**
     * Transisi status tunggal yang divalidasi ketat sesuai PRD (FR-019, FR-020).
     * Timestamp tiap status dicatat otomatis (mitigasi RISK 2).
     */
    public static function transition(Ticket $ticket, TicketStatus $to, ?int $technicianId = null, ?string $resolutionNotes = null): Ticket
    {
        $from = $ticket->status;

        if (! $from->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status tidak valid dari {$from->label()} ke {$to->label()}.",
            ]);
        }

        if ($to === TicketStatus::Escalated) {
            // BR-004: ticket yang dieskalasikan wajib memiliki Technician.
            $technician = Technician::where('status', 'active')->find($technicianId);

            if ($technician === null) {
                throw ValidationException::withMessages([
                    'technician_id' => 'Teknisi aktif wajib dipilih saat eskalasi.',
                ]);
            }

            $ticket->technician_id = $technician->id;
        }

        if ($to === TicketStatus::Resolved && trim((string) $resolutionNotes) === '') {
            throw ValidationException::withMessages([
                'resolution_notes' => 'Resolution notes wajib diisi saat menyelesaikan ticket.',
            ]);
        }

        match ($to) {
            TicketStatus::Received => $ticket->received_at = now(),
            TicketStatus::Escalated => $ticket->escalated_at = now(),
            TicketStatus::InProgress => $ticket->in_progress_at = now(),
            TicketStatus::Resolved => $ticket->resolved_at = now(),
            TicketStatus::Closed => $ticket->closed_at = now(),
            default => null,
        };

        $ticket->status = $to;
        $ticket->save();

        AuditLog::record("ticket.{$to->value}", 'ticket', $ticket->id, [
            'from' => $from->value,
            'to' => $to->value,
            'technician_id' => $to === TicketStatus::Escalated ? $ticket->technician_id : null,
        ]);

        return $ticket;
    }
}
