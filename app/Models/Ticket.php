<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'requester_name',
        'requester_contact',
        'department_id',
        'category_id',
        'problem_type_id',
        'priority_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'received_at' => 'datetime',
            'escalated_at' => 'datetime',
            'in_progress_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            $ticket->status ??= TicketStatus::Open;
            // Tracking code acak 32 karakter agar tidak mudah ditebak / di-enumerate.
            $ticket->tracking_code ??= Str::random(32);
        });

        static::created(function (Ticket $ticket): void {
            if ($ticket->ticket_number === null) {
                // Berbasis ID sehingga unik tanpa race condition.
                $ticket->ticket_number = 'TKT-'.$ticket->created_at->format('Ymd').'-'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT);
                $ticket->saveQuietly();
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function problemType(): BelongsTo
    {
        return $this->belongsTo(ProblemType::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
