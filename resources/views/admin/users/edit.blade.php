@extends('layouts.app')

@section('title', 'Редагувати користувача')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Редагувати користувача</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Оновіть інформацію про користувача</p>
    </div>

    <div class="card max-w-2xl">
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Ім'я</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label for="password">Новий пароль (залиште порожнім, щоб не змінювати)</label>
                <input type="password" id="password" name="password" placeholder="Мінімум 8 символів">
            </div>

            @if($user->role !== 'admin')
            <div class="form-group">
                <label for="role">Роль</label>
                <select id="role" name="role">
                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Користувач</option>
                    <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Менеджер</option>
                </select>
            </div>
            @else
            <div class="form-group">
                <label>Роль</label>
                <input type="text" value="Адміністратор" disabled class="bg-gray-100">
                <p class="mt-1 text-xs text-gray-500">Роль адміністратора не можна змінювати</p>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-4 flex-wrap mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Оновити користувача
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary flex-1 sm:flex-none text-center">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
