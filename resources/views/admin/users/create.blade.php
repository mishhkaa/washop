@extends('layouts.app')

@section('title', 'Додати користувача')

@section('content')
<div>
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Додати користувача</h1>
        <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Створіть нового користувача</p>
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

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">Ім'я</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Введіть ім'я">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
            </div>

            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required placeholder="Мінімум 8 символів">
            </div>

            <div class="form-group">
                <label for="role">Роль</label>
                <select id="role" name="role" required>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Користувач</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Менеджер</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Примітка: Роль "Адміністратор" може бути призначена тільки через базу даних</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 sm:gap-4 flex-wrap mt-8 pt-6 border-t border-gray-200">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">
                    Зберегти користувача
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary flex-1 sm:flex-none text-center">
                    Скасувати
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
