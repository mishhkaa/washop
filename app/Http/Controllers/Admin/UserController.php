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
        // Показуємо всіх користувачів, крім адмінів (адміни керуються окремо)
        $users = User::where('role', '!=', 'admin')
            ->orderBy('role')
            ->orderBy('name')
            ->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:user,manager',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        // Роль може бути тільки user або manager (не admin!)

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Користувач успішно створений');
    }

    public function edit(User $user)
    {
        // Захист: не дозволяємо редагувати адмінів через цей контролер
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Не можна редагувати адміністратора через цей інтерфейс');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Захист: не дозволяємо змінювати роль адміна
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Не можна змінювати дані адміністратора через цей інтерфейс');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:user,manager',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Якщо роль не вказана, залишаємо стару
        if (empty($validated['role'])) {
            unset($validated['role']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Користувач успішно оновлений');
    }

    public function destroy(User $user)
    {
        // Захист: не дозволяємо видаляти адмінів
        if ($user->role === 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Не можна видаляти адміністратора');
        }

        // Захист: не дозволяємо видаляти самого себе
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Не можна видалити самого себе');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Користувач успішно видалений');
    }
}
