<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Technician;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TicketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        Department::create(['name' => 'IT', 'status' => 'active']);
        Category::create(['name' => 'Software', 'status' => 'active']);
        Priority::create(['name' => 'Medium', 'ordering' => 3, 'status' => 'active']);
    }

    private function makeTicket(): Ticket
    {
        return Ticket::create([
            'requester_name' => 'Siti',
            'department_id' => 1,
            'category_id' => 1,
            'priority_id' => 1,
            'description' => 'Email tidak bisa dikirim.',
        ]);
    }

    public function test_full_happy_path_open_to_closed(): void
    {
        $ticket = $this->makeTicket();
        $technician = Technician::create(['name' => 'Andi', 'status' => 'active']);

        TicketWorkflow::transition($ticket, TicketStatus::Received);
        TicketWorkflow::transition($ticket, TicketStatus::Escalated, $technician->id);
        TicketWorkflow::transition($ticket, TicketStatus::InProgress);
        TicketWorkflow::transition($ticket, TicketStatus::Resolved, null, 'SMTP sudah diperbaiki.');
        TicketWorkflow::transition($ticket, TicketStatus::Closed);

        $ticket->refresh();

        $this->assertSame(TicketStatus::Closed, $ticket->status);
        $this->assertNotNull($ticket->received_at);
        $this->assertNotNull($ticket->escalated_at);
        $this->assertNotNull($ticket->in_progress_at);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertNotNull($ticket->closed_at);
        $this->assertSame($technician->id, $ticket->technician_id);

        // Setiap transisi tercatat di audit log.
        foreach (['received', 'escalated', 'in_progress', 'resolved', 'closed'] as $action) {
            $this->assertTrue(AuditLog::where('action', "ticket.{$action}")->where('entity_id', $ticket->id)->exists());
        }
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $ticket = $this->makeTicket();

        $this->expectException(ValidationException::class);

        try {
            TicketWorkflow::transition($ticket, TicketStatus::InProgress);
        } finally {
            $ticket->refresh();
            $this->assertSame(TicketStatus::Open, $ticket->status);
            $this->assertNull($ticket->in_progress_at);
        }
    }

    public function test_closed_ticket_cannot_be_reopened(): void
    {
        $ticket = $this->makeTicket();
        $technician = Technician::create(['name' => 'Andi', 'status' => 'active']);

        TicketWorkflow::transition($ticket, TicketStatus::Received);
        TicketWorkflow::transition($ticket, TicketStatus::Escalated, $technician->id);
        TicketWorkflow::transition($ticket, TicketStatus::InProgress);
        TicketWorkflow::transition($ticket, TicketStatus::Resolved, null, 'Selesai.');
        TicketWorkflow::transition($ticket, TicketStatus::Closed);

        $this->assertFalse($ticket->status->canTransitionTo(TicketStatus::Received));

        $this->expectException(ValidationException::class);
        TicketWorkflow::transition($ticket, TicketStatus::Received);
    }

    public function test_escalation_requires_an_active_technician(): void
    {
        $ticket = $this->makeTicket();
        TicketWorkflow::transition($ticket, TicketStatus::Received);

        // Tanpa teknisi.
        try {
            TicketWorkflow::transition($ticket, TicketStatus::Escalated);
            $this->fail('Eskalasi tanpa teknisi seharusnya gagal.');
        } catch (ValidationException) {
            $ticket->refresh();
            $this->assertSame(TicketStatus::Received, $ticket->status);
        }

        // Teknisi inactive.
        $inactive = Technician::create(['name' => 'Budi', 'status' => 'inactive']);
        try {
            TicketWorkflow::transition($ticket, TicketStatus::Escalated, $inactive->id);
            $this->fail('Eskalasi ke teknisi inactive seharusnya gagal.');
        } catch (ValidationException) {
            $this->assertNull($ticket->fresh()->technician_id);
        }
    }

    public function test_resolve_requires_resolution_notes(): void
    {
        $ticket = $this->makeTicket();
        $technician = Technician::create(['name' => 'Andi', 'status' => 'active']);

        TicketWorkflow::transition($ticket, TicketStatus::Received);
        TicketWorkflow::transition($ticket, TicketStatus::Escalated, $technician->id);
        TicketWorkflow::transition($ticket, TicketStatus::InProgress);

        $this->expectException(ValidationException::class);
        TicketWorkflow::transition($ticket, TicketStatus::Resolved, null, '   ');
    }

    public function test_admin_can_transition_via_endpoint_and_is_audited(): void
    {
        $ticket = $this->makeTicket();

        $response = $this->actingAs($this->admin)->post("/admin/tickets/{$ticket->id}/transition", [
            'status' => 'received',
        ]);

        $response->assertRedirect();
        $ticket->refresh();

        $this->assertSame(TicketStatus::Received, $ticket->status);
        $this->assertNotNull($ticket->received_at);

        $audit = AuditLog::where('action', 'ticket.received')->where('entity_id', $ticket->id)->first();
        $this->assertNotNull($audit);
        $this->assertSame($this->admin->email, $audit->actor);
    }

    public function test_guest_cannot_access_admin_pages(): void
    {
        $this->get('/admin/tickets')->assertRedirect(route('login'));
        $this->get('/admin/audit-logs')->assertRedirect(route('login'));
    }
}
