<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('ports')
            ->latest()
            ->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['ports.node', 'transactions']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'balance' => 'required|integer|min:0',
            'is_admin' => 'boolean',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diupdate.');
    }

    public function addBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1000',
            'description' => 'required|string',
        ]);

        $user->addBalance($validated['amount']);

        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'balance_after' => $user->balance,
        ]);

        return back()->with('success', 'Saldo berhasil ditambahkan.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password berhasil direset.');
    }

    public function destroy(User $user)
    {
        if ($user->ports()->active()->exists()) {
            return back()->with('error', 'User masih memiliki port aktif.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
