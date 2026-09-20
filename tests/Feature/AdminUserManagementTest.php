<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($this->admin);
    }

    public function test_create_update_permissions_password_and_delete(): void
    {
        $email = 'manager-'.bin2hex(random_bytes(5)).'@example.test';
        $this->from(route('admin.users.index'))->post(route('admin.users.store'), [
            '_form' => 'create', 'name' => 'New Manager', 'email' => $email, 'password' => 'Password123!',
            'role' => 'manager', 'permissions' => array_keys(User::permissionOptions()),
        ])->assertSessionHasNoErrors()->assertSessionHas('active_role', 'manager')->assertRedirect(route('admin.users.index'));
        $user = User::where('email', $email)->firstOrFail();
        $this->assertTrue($user->hasPermission('products'));
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $originalHash = $user->password;
        $data = ['_editing_user' => $user->id, 'name' => 'Edited Admin', 'email' => $email, 'role' => 'admin'];
        $this->put(route('admin.users.update', $user), [...$data, 'password' => '', 'permissions' => ['products', 'site_tracking']])
            ->assertSessionHasNoErrors()->assertSessionHas('active_role', 'admin');
        $user->refresh();
        $this->assertSame($originalHash, $user->password);
        $this->assertSame(['products', 'site_tracking'], $user->permissions);
        $this->put(route('admin.users.update', $user), [...$data, 'password' => 'ChangedPass123!'])->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame([], $user->permissions);
        $this->assertTrue(Hash::check('ChangedPass123!', $user->password));
        $this->delete(route('admin.users.destroy', $user))->assertRedirect();
        $this->assertModelMissing($user);
    }

    public function test_update_errors_stay_on_correct_user_instead_of_opening_create_modal(): void
    {
        $user = User::factory()->create(['role' => 'manager', 'permissions' => ['products']]);
        $this->from(route('admin.users.index'))->put(route('admin.users.update', $user), [
            '_editing_user' => $user->id, 'name' => 'Keep This Edit', 'email' => 'invalid',
            'role' => 'manager', 'permissions' => ['products'],
        ])->assertSessionHasErrors(['email'], null, 'updateUser'.$user->id);
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Keep This Edit')
            ->assertViewHas('users', fn ($users) => $users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->perPage() === 15)
            ->assertSee('class="role-tab active" role="tab" aria-selected="true" data-role="manager"', false)
            ->assertDontSee('class="modal-shell show"', false);
    }

    public function test_create_errors_restore_selected_role_and_permissions(): void
    {
        $this->from(route('admin.users.index'))->post(route('admin.users.store'), [
            '_form' => 'create', 'name' => 'Saved Form Name', 'email' => 'invalid',
            'password' => 'short', 'role' => 'admin', 'permissions' => ['products'],
        ])->assertSessionHasErrors(['email', 'password'], null, 'createUser');
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Saved Form Name')->assertSee('class="modal-shell show"', false);
    }

    public function test_protected_accounts_and_user_management_permissions(): void
    {
        $this->delete(route('admin.users.destroy', $this->admin))->assertUnprocessable();
        $this->put(route('admin.users.update', $this->admin), ['name' => $this->admin->name, 'email' => $this->admin->email, 'role' => 'manager'])
            ->assertSessionHasErrors(['role'], null, 'updateUser'.$this->admin->id);
        $this->assertSame('super_admin', $this->admin->fresh()->role);
        $manager = User::factory()->create(['role' => 'manager', 'permissions' => array_keys(User::permissionOptions())]);
        $this->actingAs($manager);
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->post(route('admin.users.store'), [])->assertForbidden();
        $this->delete(route('admin.users.destroy', $this->admin))->assertForbidden();
    }

    public function test_products_and_tracking_users_land_on_their_allowed_pages(): void
    {
        foreach (['products' => route('admin.products.index'), 'site_tracking' => route('admin.tracking.edit', 'visitors')] as $permission => $destination) {
            $this->post(route('logout'));
            $user = User::factory()->create(['role' => 'manager', 'permissions' => [$permission], 'password' => 'LoginPass123!']);
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'LoginPass123!'])->assertRedirect($destination);
            $this->get($destination)->assertOk();
        }
    }
}
