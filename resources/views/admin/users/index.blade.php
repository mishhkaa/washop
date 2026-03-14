@extends('layouts.app')

@section('title', 'Користувачі')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Користувачі</h1>
            <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-600">Управління користувачами</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn-primary w-full sm:w-auto">
            + Додати користувача
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Desktop Table -->
    <div class="hidden md:block card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ім'я</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Роль</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Дії</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $user->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-purple-600 font-medium text-sm">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->role == 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $user->role == 'manager' ? 'Менеджер' : 'Користувач' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-edit">Редагувати</a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Ви впевнені?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-delete">Видалити</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Користувачі не знайдені</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4">
        @forelse($users as $user)
            <div class="card">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                        <span class="text-purple-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->role == 'manager' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $user->role == 'manager' ? 'Менеджер' : 'Користувач' }}
                        </span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="flex-1 btn-edit text-center">Редагувати</a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Ви впевнені?')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full btn-delete">Видалити</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card text-center py-12">
                <p class="text-sm text-gray-500">Користувачі не знайдені</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
