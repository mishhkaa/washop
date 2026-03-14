<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CalculationController extends Controller
{
    /**
     * Сторінка розрахунку для менеджера: баланс та історія виплат.
     * Баланс збільшується при продажах, зменшується при виплатах (розрахунках) від адміна.
     */
    public function index()
    {
        $user = Auth::user();
        $user->refresh(); // актуальний баланс з БД

        $currentBalance = (float) ($user->balance ?? 0);

        $payments = \App\Models\Payment::where('manager_id', $user->id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return view('manager.calculation.index', compact('currentBalance', 'payments'));
    }
}
