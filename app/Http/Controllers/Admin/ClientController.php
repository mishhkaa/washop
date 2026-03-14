<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount('sales');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qry) use ($q) {
                $qry->where('telegram_username', 'like', "%{$q}%")
                    ->orWhere('telegram_user_id', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function show(Client $client)
    {
        $client->loadCount('sales');
        $client->load(['sales' => fn ($q) => $q->with(['saleItems.product', 'manager'])->orderBy('created_at', 'desc')->limit(50)]);
        return view('admin.clients.show', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'cashback_percent' => 'nullable|integer|min:0|max:100',
        ]);
        $client->update($validated);
        return back()->with('success', 'Дані клієнта оновлено.');
    }
}
