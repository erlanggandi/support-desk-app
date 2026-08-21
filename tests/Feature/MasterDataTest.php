<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_update_and_delete_department(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/master/departments', [
            'name' => 'SDM',
            'description' => 'Human Resources',
            'status' => 'active',
        ])->assertRedirect();

        $department = Department::where('name', 'SDM')->firstOrFail();
        $this->assertTrue(AuditLog::where('action', 'master_data.created')->where('entity_id', $department->id)->exists());

        $this->put("/admin/master/departments/{$department->id}", [
            'name' => 'SDM',
            'description' => null,
            'status' => 'inactive',
        ])->assertRedirect();

        $this->assertSame('inactive', $department->fresh()->status);

        $this->delete("/admin/master/departments/{$department->id}")->assertRedirect();
        $this->assertSame(0, Department::count());
        $this->assertTrue(AuditLog::where('action', 'master_data.deleted')->exists());
    }

    public function test_validation_rejects_missing_name(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post('/admin/master/departments', ['status' => 'active']);

        $response->assertSessionHasErrors('name');
    }

    public function test_unknown_entity_returns_404(): void
    {
        $this->actingAs($this->admin);
        $this->get('/admin/master/unknown')->assertNotFound();
    }

    public function test_guest_cannot_manage_master_data(): void
    {
        $this->get('/admin/master/departments')->assertRedirect(route('login'));
        $this->post('/admin/master/departments', ['name' => 'X'])->assertRedirect(route('login'));
    }
}
