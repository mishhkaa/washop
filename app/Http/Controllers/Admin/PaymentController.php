<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create(User $manager)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        return view('admin.payments.create', compact('manager'));
    }

    public function store(Request $request, User $manager)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['manager_id'] = $manager->id;
        
        // Використовуємо транзакцію: спочатку виплата, потім мінус з балансу менеджера
        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $manager) {
            $amount = (float) $validated['amount'];
            // Блокуємо рядок менеджера та читаємо актуальний баланс
            $managerRow = User::where('id', $manager->id)->lockForUpdate()->first();
            if (!$managerRow) {
                throw new \Exception('Менеджера не знайдено');
            }
            $currentBalance = (float) ($managerRow->balance ?? 0);
            if ($currentBalance < $amount) {
                throw new \Exception('Недостатньо коштів на балансі менеджера. Доступно: ' . number_format($currentBalance, 2) . ' zł');
            }
            Payment::create($validated);
            $managerRow->decrement('balance', $amount);
        });

        return redirect()->route('admin.managers.calculation', $manager)
            ->with('success', 'Виплата успішно додана');
    }

    public function index(User $manager)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        $payments = Payment::where('manager_id', $manager->id)
            ->orderBy('payment_date', 'desc')
            ->get();
        
        return view('admin.payments.index', compact('manager', 'payments'));
    }

    public function edit(User $manager, Payment $payment)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        if ($payment->manager_id !== $manager->id) {
            abort(404);
        }
        
        return view('admin.payments.edit', compact('manager', 'payment'));
    }

    public function update(Request $request, User $manager, Payment $payment)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        if ($payment->manager_id !== $manager->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'period_from' => 'required|date',
            'period_to' => 'required|date|after_or_equal:period_from',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment->update($validated);

        return redirect()->route('admin.managers.payments', $manager)
            ->with('success', 'Виплата успішно оновлена');
    }

    public function destroy(User $manager, Payment $payment)
    {
        if ($manager->role !== 'manager') {
            abort(404);
        }
        
        if ($payment->manager_id !== $manager->id) {
            abort(404);
        }
        
        $payment->delete();

        return redirect()->route('admin.managers.payments', $manager)
            ->with('success', 'Виплата успішно видалена');
    }
}
