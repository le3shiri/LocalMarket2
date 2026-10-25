<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('created_at', 'desc');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('q')) {
            $search = '%'.$request->q.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('staff.users.index', compact('users'));
    }

    public function create()
    {
        return view('staff.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ADMIN, User::WAREHOUSE, User::CASHIER, User::CUSTOMER])],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => $request->has('is_active'),
        ]);

        AuditLog::record('user.created', $user, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('staff.users.index')
            ->with('success', "Usuario {$user->name} creado correctamente.");
    }

    public function edit(User $user)
    {
        return view('staff.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'role' => ['required', Rule::in([User::ADMIN, User::WAREHOUSE, User::CASHIER, User::CUSTOMER])],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        AuditLog::record('user.updated', $user, [
            'name' => $user->name,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ]);

        return redirect()->route('staff.users.index')
            ->with('success', "Usuario {$user->name} actualizado con éxito.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta de administrador.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::record('user.toggled_active', $user, [
            'is_active' => $user->is_active,
        ]);

        $statusStr = $user->is_active ? 'activada' : 'desactivada';

        return back()->with('success', "La cuenta de {$user->name} ha sido {$statusStr}.");
    }
}
