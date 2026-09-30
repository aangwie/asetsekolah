<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::with('roles')->orderBy('name')->paginate(15)]);
    }

    public function create(): View { return view('users.form', ['user' => new User, 'roles' => Role::pluck('name')]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);
        $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $user->assignRole($data['role']);
        return redirect()->route('users.index')->with('ok', 'User simpan.');
    }

    public function edit(User $user): View { return view('users.form', ['user' => $user, 'roles' => Role::pluck('name')]); }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);
        $user->update(['name' => $data['name'], 'email' => $data['email']] + ($data['password'] ? ['password' => $data['password']] : []));
        $user->syncRoles([$data['role']]);
        return redirect()->route('users.index')->with('ok', 'User update.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return back()->with('ok', 'User hapus.');
    }
}
