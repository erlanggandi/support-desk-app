<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\ProblemType;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTicketTest extends TestCase
{
    use RefreshDatabase;

    private int $departmentId;
    private int $categoryId;
    private int $priorityId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->departmentId = Department::create(['name' => 'Keuangan', 'status' => 'active'])->id;
        $this->categoryId = Category::create(['name' => 'Hardware', 'status' => 'active'])->id;
        $this->priorityId = Priority::create(['name' => 'High', 'ordering' => 2, 'status' => 'active'])->id;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'requester_name' => 'Budi Santoso',
            'department_id' => $this->departmentId,
            'category_id' => $this->categoryId,
            'priority_id' => $this->priorityId,
            'description' => 'Laptop tidak bisa menyala sejak pagi.',
        ], $overrides);
    }

    public function test_guest_can_create_ticket_and_receives_credentials(): void
    {
        $this->get('/tickets/create');
        $token = session('ticket_form_token');

        $response = $this->post('/tickets', [...$this->validPayload(), 'form_token' => $token]);

        $response->assertRedirect(route('tickets.success'));

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertSame('open', $ticket->status->value);
        $this->assertMatchesRegularExpression('/^TKT-\d{8}-\d{4}$/', $ticket->ticket_number);
        $this->assertSame(32, strlen($ticket->tracking_code));

        // AC-009: audit log tercatat saat pembuatan ticket.
        $this->assertTrue(AuditLog::where('action', 'ticket.created')->where('entity_id', $ticket->id)->exists());
    }

    public function test_double_submit_is_rejected_with_one_time_token(): void
    {
        $this->get('/tickets/create');
        $token = session('ticket_form_token');

        $this->post('/tickets', [...$this->validPayload(), 'form_token' => $token]);
        $response = $this->post('/tickets', [...$this->validPayload(), 'form_token' => $token]);

        $response->assertSessionHasErrors('form_token');
        $this->assertSame(1, Ticket::count());
    }

    public function test_inactive_master_data_is_rejected(): void
    {
        Department::whereKey($this->departmentId)->update(['status' => 'inactive']);

        $this->get('/tickets/create');
        $response = $this->post('/tickets', [...$this->validPayload(), 'form_token' => session('ticket_form_token')]);

        $response->assertSessionHasErrors('department_id');
        $this->assertSame(0, Ticket::count());
    }

    public function test_required_fields_are_validated(): void
    {
        $this->get('/tickets/create');
        $response = $this->post('/tickets', ['form_token' => session('ticket_form_token')]);

        $response->assertSessionHasErrors(['requester_name', 'department_id', 'category_id', 'priority_id', 'description']);
    }

    public function test_tracking_with_valid_credentials_shows_ticket(): void
    {
        $ticket = Ticket::create($this->validPayload());

        $response = $this->post('/track', [
            'ticket_number' => $ticket->ticket_number,
            'tracking_code' => $ticket->tracking_code,
        ]);

        $response->assertInertia(
            fn ($page) => $page
                ->component('public/tickets/track')
                ->where('ticket.ticket_number', $ticket->ticket_number)
                ->where('ticket.status_label', 'Open')
                ->has('ticket.timeline')
        );
    }

    public function test_tracking_with_wrong_code_does_not_leak_ticket_existence(): void
    {
        $ticket = Ticket::create($this->validPayload());

        $response = $this->from('/track')->post('/track', [
            'ticket_number' => $ticket->ticket_number,
            'tracking_code' => str_repeat('x', 32),
        ]);

        $response->assertRedirect('/track');
        $response->assertSessionHasErrors('tracking');
        $this->assertStringContainsString(
            'tidak ditemukan',
            (string) session('errors')->default->first('tracking'),
        );
    }

    public function test_success_page_requires_flash_data(): void
    {
        $this->get(route('tickets.success'))->assertRedirect(route('tickets.track'));
    }
}
