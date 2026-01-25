<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->employee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_employee_can_view_profile(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('profile.show'));

        $response->assertOk();
        $response->assertViewIs('profile.show');
        $response->assertViewHas('user');
        $response->assertSee($this->employee->first_name);
        $response->assertSee($this->employee->last_name);
    }

    public function test_employee_can_update_profile(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '123-456-7890',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->employee->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '123-456-7890',
        ]);
    }

    public function test_employee_can_change_password(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => $this->employee->email,
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $this->employee->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->employee->password));
    }

    public function test_employee_cannot_change_password_with_wrong_current(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => $this->employee->email,
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_email_must_be_unique(): void
    {
        $otherUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'taken@example.com',
        ]);

        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_employee_can_use_same_email(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $this->employee->email, // Same email
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');
    }

    public function test_profile_requires_first_name(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => '',
            'last_name' => $this->employee->last_name,
            'email' => $this->employee->email,
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_profile_requires_last_name(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => $this->employee->first_name,
            'last_name' => '',
            'email' => $this->employee->email,
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_profile_requires_valid_email(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('profile.update'), [
            'first_name' => $this->employee->first_name,
            'last_name' => $this->employee->last_name,
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_unauthenticated_user_cannot_access_profile(): void
    {
        $response = $this->get(route('profile.show'));

        $response->assertRedirect(route('login'));
    }
}
