<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $fallbackRole = session('active_role')
            ?? User::whereKey($request->old('_editing_user'))->value('role')
            ?? 'super_admin';
        $requestedRole = $request->query('role', $fallbackRole);
        $activeRole = in_array($requestedRole, ['super_admin', 'admin', 'manager'], true) ? $requestedRole : 'super_admin';

        return view('dasgboard.pages.admin-users', [
            'users' => User::where('role', $activeRole)->latest()->paginate(15)->withQueryString(),
            'activeRole' => $activeRole,
            'roleCounts' => User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('createUser', ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', 'unique:users'], 'password' => ['required', 'string', 'min:8'], 'role' => ['required', 'in:super_admin,admin,manager'], 'permissions' => ['nullable', 'array'], 'permissions.*' => [Rule::in(array_keys(User::permissionOptions()))]]);
        $data['permissions'] = $data['role'] === 'super_admin' ? array_keys(User::permissionOptions()) : ($data['permissions'] ?? []);
        User::create($data);

        return back()->with('success', 'User created successfully.')->with('active_role', $data['role']);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validateWithBag('updateUser'.$user->id, ['name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)], 'password' => ['nullable', 'string', 'min:8'], 'role' => ['required', 'in:super_admin,admin,manager'], 'permissions' => ['nullable', 'array'], 'permissions.*' => [Rule::in(array_keys(User::permissionOptions()))]]);
        if ($user->isSuperAdmin() && $data['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'The Super Admin role cannot be changed.'], 'updateUser'.$user->id)->withInput($request->except('password'));
        }
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $data['permissions'] = $data['role'] === 'super_admin' ? array_keys(User::permissionOptions()) : ($data['permissions'] ?? []);
        $user->update($data);

        return back()->with('success', 'User updated successfully.')->with('active_role', $user->role);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        abort_if($user->isSuperAdmin(), 422, 'The Super Admin cannot be deleted.');
        $role = $user->role;
        $user->delete();

        return back()->with('success', 'User deleted successfully.')->with('active_role', $role);
    }
}
