<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\SaleController as ManagerSaleController;
use App\Http\Controllers\Manager\ProductController as ManagerProductController;
use App\Http\Controllers\Manager\CalculationController as ManagerCalculationController;
use App\Http\Controllers\Admin\BotController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\ShopController;

// ========== МАГАЗИН (головний сайт) — корінь ==========
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['pl', 'uk', 'en'], true)) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/', [ShopController::class, 'index'])->name('shop.home');
Route::get('/cart', fn () => redirect()->route('shop.home'))->name('shop.cart');
Route::post('/cart/add', [ShopController::class, 'addToCart'])->name('shop.cart.add');
Route::post('/cart/update', [ShopController::class, 'updateCart'])->name('shop.cart.update');
Route::post('/cart/remove', [ShopController::class, 'removeFromCart'])->name('shop.cart.remove');
Route::post('/cart/delivery', [ShopController::class, 'setCartDelivery'])->name('shop.cart.delivery');
Route::post('/shop/set-telegram', [ShopController::class, 'setTelegramSession'])->name('shop.set-telegram');
Route::get('/checkout', [ShopController::class, 'checkoutForm'])->name('shop.checkout.form');
Route::post('/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');

// ========== CRM — під префіксом /crm ==========
Route::prefix('crm')->group(function () {
    // Корінь CRM: якщо вже увійшов — на дашборд, інакше — логін
    Route::get('/', function () {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if ($user->role === 'manager') {
                return redirect()->route('manager.dashboard');
            }
        }
        return redirect()->route('login');
    })->name('index');

    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
            Route::resource('categories', CategoryController::class);
            Route::resource('products', ProductController::class);
            Route::get('managers/{manager}/stock', [ManagerController::class, 'show'])->name('managers.stock');
            Route::get('managers/{manager}/calculation', [ManagerController::class, 'calculation'])->name('managers.calculation');
            Route::get('managers/{manager}/payments', [PaymentController::class, 'index'])->name('managers.payments');
            Route::get('managers/{manager}/payments/create', [PaymentController::class, 'create'])->name('managers.payments.create');
            Route::post('managers/{manager}/payments', [PaymentController::class, 'store'])->name('managers.payments.store');
            Route::get('managers/{manager}/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('managers.payments.edit');
            Route::put('managers/{manager}/payments/{payment}', [PaymentController::class, 'update'])->name('managers.payments.update');
            Route::delete('managers/{manager}/payments/{payment}', [PaymentController::class, 'destroy'])->name('managers.payments.destroy');
            Route::resource('managers', ManagerController::class)->except(['show']);
            Route::resource('sales', SaleController::class)->except(['show']);
            Route::resource('clients', ClientController::class)->only(['index', 'show', 'update']);
            Route::get('shop', [BotController::class, 'index'])->name('bot.index'); // Магазин / ТГ-бот (головна розділу)
            Route::resource('shop/categories', ShopCategoryController::class)->parameters(['categories' => 'shopCategory'])->names('shop.categories');
        });

            // Менеджери теж можуть відкрити замовлення і підтвердити/оновити статус
            Route::prefix('admin')->name('admin.')->middleware('staff')->group(function () {
                Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
                Route::post('sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('sales.update-status');
            });

        Route::prefix('manager')->name('manager.')->middleware('manager')->group(function () {
            Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
            Route::get('/calculation', [ManagerCalculationController::class, 'index'])->name('calculation');
            Route::resource('products', ManagerProductController::class)->only(['index', 'show']);
            Route::resource('sales', ManagerSaleController::class);
        });
    });
});
