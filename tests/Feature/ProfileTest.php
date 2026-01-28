<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'password' => Hash::make('password123'),
        ]);
        UserRoleAssignment::create([
            'user_id' => $this->user->id,
            'system_role' => SystemRole::Employee->value,
        ]);
    }

    public function test_user_can_view_profile(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.index'));

        $response->assertOk();
        $response->assertViewIs('profile.index');
        $response->assertSee($this->user->full_name);
        $response->assertSee($this->user->email);
    }

    public function test_user_can_view_profile_edit_form(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.edit'));

        $response->assertOk();
        $response->assertViewIs('profile.edit');
    }

    public function test_user_can_update_contact_details(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '123-456-7890',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertEquals('Updated', $this->user->first_name);
        $this->assertEquals('Name', $this->user->last_name);
        $this->assertEquals('updated@example.com', $this->user->email);
        $this->assertEquals('123-456-7890', $this->user->phone);
    }

    public function test_user_cannot_update_with_invalid_email(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_use_duplicate_email(): void
    {
        $otherUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->user);

        $response = $this->put(route('profile.update'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $otherUser->email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_view_password_change_form(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.password'));

        $response->assertOk();
        $response->assertViewIs('profile.change-password');
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->user->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_user_cannot_change_password_without_confirmation(): void
    {
        $this->actingAs($this->user);

        $response = $this->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post(route('profile.avatar.update'), [
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNotNull($this->user->avatar_path);
        Storage::disk('public')->assertExists($this->user->avatar_path);
    }

    public function test_user_cannot_upload_invalid_file_as_avatar(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('profile.avatar.update'), [
            'avatar' => $file,
        ]);

        $response->assertSessionHasErrors('avatar');
    }

    public function test_user_can_delete_avatar(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $file = UploadedFile::fake()->image('avatar.jpg');
        $path = $file->store('avatars', 'public');
        $this->user->update(['avatar_path' => $path]);

        $response = $this->delete(route('profile.avatar.delete'));

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success');

        $this->user->refresh();
        $this->assertNull($this->user->avatar_path);
    }

    public function test_profile_shows_system_roles(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('profile.index'));

        $response->assertOk();
        $response->assertSee('Employee');
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get(route('profile.index'));

        $response->assertRedirect(route('login'));
    }
}
