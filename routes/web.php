<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\CategoryController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\InventoryController;
use App\Http\Controllers\Staff\ProductController;
use App\Http\Controllers\Staff\StoreSaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LocalMarket
|--------------------------------------------------------------------------
*/

// Public Shop Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/producto/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/cesta', function () {
    return 'Cesta en construcción (Sprint 4)';
})->name('cart.index');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/registro', [RegisterController::class, 'show'])->name('register');
    Route::post('/registro', [RegisterController::class, 'register']);

    Route::get('/olvide-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/olvide-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/restablecer-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/restablecer-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// Authenticated Routes
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/mis-pedidos', function () {
        return 'Mis pedidos (Sprint 4)';
    })->name('account.orders');
});

// Staff Management Routes (Role-based access)
Route::prefix('gestion')->middleware(['auth', 'role:admin,warehouse,cashier'])->name('staff.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pedidos', function () {
        return 'Gestión de pedidos (Sprint 4)';
    })->name('orders.index');

    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('/inventario', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventario/movimientos', [InventoryController::class, 'movements'])->name('inventory.movements');

        Route::get('/inventario/entrada', [InventoryController::class, 'createReceipt'])->name('inventory.receipt');
        Route::post('/inventario/entrada', [InventoryController::class, 'storeReceipt']);

        Route::get('/inventario/traspaso', [InventoryController::class, 'createTransfer'])->name('inventory.transfer');
        Route::post('/inventario/traspaso', [InventoryController::class, 'storeTransfer']);

        Route::get('/inventario/ajuste', [InventoryController::class, 'createAdjustment'])->name('inventory.adjustment');
        Route::post('/inventario/ajuste', [InventoryController::class, 'storeAdjustment']);

        Route::get('/lotes', [InventoryController::class, 'batches'])->name('batches.index');
    });

    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/tpv', [StoreSaleController::class, 'create'])->name('pos.create');
        Route::post('/tpv', [StoreSaleController::class, 'store'])->name('pos.store');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('productos', ProductController::class)->names('products')->parameters(['productos' => 'product']);
        Route::resource('categorias', CategoryController::class)->names('categories')->parameters(['categorias' => 'category']);

        Route::get('/cupones', function () {
            return 'Gestión de cupones (Sprint 6)';
        })->name('coupons.index');

        Route::get('/usuarios', function () {
            return 'Gestión de usuarios (Sprint 6)';
        })->name('users.index');

        Route::get('/auditoria', function () {
            return 'Registro de auditoría (Sprint 6)';
        })->name('audit.index');
    });
});
