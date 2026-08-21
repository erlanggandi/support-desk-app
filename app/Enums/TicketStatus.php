<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Received = 'received';
    case Escalated = 'escalated';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Received => 'Received',
            self::Escalated => 'Escalated',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    /**
     * Workflow ketat sesuai PRD:
     * Open → Received → Escalated → In Progress → Resolved → Closed.
     * Tidak ada Reopened.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Open => $next === self::Received,
            self::Received => $next === self::Escalated,
            self::Escalated => $next === self::InProgress,
            self::InProgress => $next === self::Resolved,
            self::Resolved => $next === self::Closed,
            self::Closed => false,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Open->value => self::Open->label(),
            self::Received->value => self::Received->label(),
            self::Escalated->value => self::Escalated->label(),
            self::InProgress->value => self::InProgress->label(),
            self::Resolved->value => self::Resolved->label(),
            self::Closed->value => self::Closed->label(),
        ];
    }
}
