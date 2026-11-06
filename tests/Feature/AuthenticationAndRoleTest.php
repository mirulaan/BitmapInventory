<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('BITMAP');
        $response->assertSee('Sign In');
        $response->assertSee('Username');
        $response->assertSee('@admin');
        $response->assertSee('@warehouse');
        $response->assertSee('@engineer');
        $response->assertSee('@viewer');
    }

    public function test_authenticated_user_visiting_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_authenticate_with_valid_username_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'admin_test',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'username' => 'admin_test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'admin_test',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'admin_test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->inactive()->create([
            'username' => 'deactivated_user',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => 'deactivated_user',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
    }

    public function test_inactive_user_session_is_invalidated_by_role_middleware(): void
    {
        $inactiveUser = User::factory()->inactive()->create();

        $response = $this->actingAs($inactiveUser)->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_super_admin_has_full_access_to_all_features(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get(route('inventory.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('products.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('locations.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('clients.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('inventory.audit'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.index'))->assertStatus(200);
    }

    public function test_warehouse_manager_can_receive_stock_and_manage_products_and_locations(): void
    {
        $warehouse = User::factory()->warehouse()->create();

        $this->actingAs($warehouse)->get(route('inventory.create'))->assertStatus(200);
        $this->actingAs($warehouse)->get(route('products.create'))->assertStatus(200);
        $this->actingAs($warehouse)->get(route('locations.create'))->assertStatus(200);
        $this->actingAs($warehouse)->get(route('clients.create'))->assertStatus(200);
    }

    public function test_engineer_cannot_receive_stock_or_create_products_but_can_manage_clients(): void
    {
        $engineer = User::factory()->engineer()->create();

        // Engineer forbidden from receiving stock, creating catalog products, or locations
        $this->actingAs($engineer)->get(route('inventory.create'))->assertStatus(403);
        $this->actingAs($engineer)->get(route('products.create'))->assertStatus(403);
        $this->actingAs($engineer)->get(route('locations.create'))->assertStatus(403);

        // Engineer can view inventory, catalog, and manage clients
        $this->actingAs($engineer)->get(route('inventory.index'))->assertStatus(200);
        $this->actingAs($engineer)->get(route('products.index'))->assertStatus(200);
        $this->actingAs($engineer)->get(route('clients.create'))->assertStatus(200);
    }

    public function test_viewer_has_read_only_access_and_is_forbidden_from_mutations(): void
    {
        $viewer = User::factory()->viewer()->create();

        // Viewer can view
        $this->actingAs($viewer)->get('/dashboard')->assertStatus(200);
        $this->actingAs($viewer)->get(route('inventory.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('products.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('inventory.audit'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('categories.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('locations.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('clients.index'))->assertStatus(200);

        // Viewer is forbidden from all create / mutation endpoints
        $this->actingAs($viewer)->get(route('inventory.create'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('products.create'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('locations.create'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('clients.create'))->assertStatus(403);
    }

    public function test_unauthenticated_requests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
        $this->get('/products')->assertRedirect('/login');
        $this->get('/audit')->assertRedirect('/login');
        $this->get('/settings/categories')->assertRedirect('/login');
        $this->get('/settings/locations')->assertRedirect('/login');
        $this->get('/settings/clients')->assertRedirect('/login');
        $this->get('/settings/users')->assertRedirect('/login');
    }

    public function test_user_can_reset_password_using_valid_superadmin_code(): void
    {
        $user = User::factory()->create([
            'username' => 'operator_jane',
            'password' => bcrypt('old-password'),
            'password_reset_code' => 'RESET123',
            'password_reset_code_expires_at' => now()->addDay(),
        ]);

        $response = $this->post(route('password.update_with_code'), [
            'username' => 'operator_jane',
            'code' => 'reset123', // case-insensitive support
            'password' => 'new-secret-pass',
            'password_confirmation' => 'new-secret-pass',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-pass', $user->password));
        $this->assertNull($user->password_reset_code);
        $this->assertNull($user->password_reset_code_expires_at);

        // Test login with the newly reset password
        $loginResponse = $this->post('/login', [
            'username' => 'operator_jane',
            'password' => 'new-secret-pass',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_reset_password_with_invalid_or_expired_code(): void
    {
        $user = User::factory()->create([
            'username' => 'operator_bob',
            'password_reset_code' => 'VALIDCODE',
            'password_reset_code_expires_at' => now()->subMinutes(10), // expired
        ]);

        // 1. Expired code test
        $response = $this->from(route('password.reset_code'))->post(route('password.update_with_code'), [
            'username' => 'operator_bob',
            'code' => 'VALIDCODE',
            'password' => 'new-pass-123',
            'password_confirmation' => 'new-pass-123',
        ]);

        $response->assertSessionHasErrors('code');

        // 2. Invalid code test
        $user->update(['password_reset_code_expires_at' => now()->addDay()]);
        $invalidResponse = $this->from(route('password.reset_code'))->post(route('password.update_with_code'), [
            'username' => 'operator_bob',
            'code' => 'WRONGCODE',
            'password' => 'new-pass-123',
            'password_confirmation' => 'new-pass-123',
        ]);

        $invalidResponse->assertSessionHasErrors('code');
    }

    public function test_reset_code_is_strictly_single_use(): void
    {
        $user = User::factory()->create([
            'username' => 'operator_sam',
            'password_reset_code' => 'ONEUSE99',
            'password_reset_code_expires_at' => now()->addDay(),
        ]);

        // First use succeeds
        $first = $this->post(route('password.update_with_code'), [
            'username' => 'operator_sam',
            'code' => 'ONEUSE99',
            'password' => 'new-pass-111',
            'password_confirmation' => 'new-pass-111',
        ]);
        $first->assertRedirect(route('login'));

        // Second use with the same code must fail
        $second = $this->from(route('password.reset_code'))->post(route('password.update_with_code'), [
            'username' => 'operator_sam',
            'code' => 'ONEUSE99',
            'password' => 'another-pass-222',
            'password_confirmation' => 'another-pass-222',
        ]);
        $second->assertSessionHasErrors('code');
    }

    public function test_super_admin_can_generate_and_revoke_reset_code_in_user_management(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->viewer()->create([
            'username' => 'target_viewer',
            'password_reset_code' => null,
            'password_reset_code_expires_at' => null,
        ]);

        // Generate code
        $response = $this->actingAs($admin)->post(route('users.generate_reset_code', $targetUser->id));
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertNotNull($targetUser->password_reset_code);
        $this->assertNotNull($targetUser->password_reset_code_expires_at);
        $this->assertTrue($targetUser->password_reset_code_expires_at->isFuture());

        // Revoke code
        $revokeResponse = $this->actingAs($admin)->post(route('users.revoke_reset_code', $targetUser->id));
        $revokeResponse->assertRedirect(route('users.index'));

        $targetUser->refresh();
        $this->assertNull($targetUser->password_reset_code);
        $this->assertNull($targetUser->password_reset_code_expires_at);
    }

    public function test_super_admin_can_manage_users(): void
    {
        $admin = User::factory()->admin()->create();

        // 1. Create new user
        $storeResponse = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Charlie Engineer',
            'username' => 'charlie_eng',
            'email' => 'charlie@bitmap.com',
            'role' => 'engineer',
            'password' => 'password123',
            'is_active' => '1',
        ]);
        $storeResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['username' => 'charlie_eng', 'email' => 'charlie@bitmap.com']);

        $charlie = User::where('username', 'charlie_eng')->first();

        // 2. Edit user
        $updateResponse = $this->actingAs($admin)->put(route('users.update', $charlie->id), [
            'name' => 'Charlie Senior Engineer',
            'username' => 'charlie_eng',
            'email' => 'charlie@bitmap.com',
            'role' => 'engineer',
            'is_active' => '1',
        ]);
        $updateResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['name' => 'Charlie Senior Engineer']);

        // 3. Toggle Status
        $this->actingAs($admin)->post(route('users.toggle_status', $charlie->id));
        $this->assertFalse($charlie->fresh()->is_active);

        // 4. Delete user
        $deleteResponse = $this->actingAs($admin)->delete(route('users.destroy', $charlie->id));
        $deleteResponse->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $charlie->id]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $warehouse = User::factory()->warehouse()->create();
        $engineer = User::factory()->engineer()->create();
        $viewer = User::factory()->viewer()->create();

        // Warehouse manager forbidden
        $this->actingAs($warehouse)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($warehouse)->get(route('users.create'))->assertStatus(403);
        $this->actingAs($warehouse)->post(route('users.generate_reset_code', $engineer->id))->assertStatus(403);

        // Engineer forbidden
        $this->actingAs($engineer)->get(route('users.index'))->assertStatus(403);

        // Viewer forbidden
        $this->actingAs($viewer)->get(route('users.index'))->assertStatus(403);
    }
}
