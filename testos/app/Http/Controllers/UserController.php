<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('nisn', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('pengaturan.users.index', compact('users'));
    }

    public function create()
    {
        $roles = User::AVAILABLE_ROLES;

        return view('pengaturan.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::AVAILABLE_ROLES))],
            'nisn' => 'nullable|string|max:30',
            'kelas' => 'nullable|string|max:50',
            'program_keahlian' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'nisn' => $validated['nisn'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'program_keahlian' => $validated['program_keahlian'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('pengaturan.users.index')->with('success', "Akun {$user->role_label} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        $roles = User::AVAILABLE_ROLES;

        return view('pengaturan.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::AVAILABLE_ROLES))],
            'nisn' => 'nullable|string|max:30',
            'kelas' => 'nullable|string|max:50',
            'program_keahlian' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nisn' => $validated['nisn'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'program_keahlian' => $validated['program_keahlian'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('pengaturan.users.index')->with('success', "Akun {$user->role_label} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('pengaturan.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
