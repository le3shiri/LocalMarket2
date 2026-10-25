<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LocalMarket
|--------------------------------------------------------------------------
*/

// Public Shop Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/catalogo', function () {
    return 'Catálogo en construcción';
})->name('catalog.index');

Route::get('/cesta', function () {
    return 'Cesta en construcción';
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
        return 'Mis pedidos';
    })->name('account.orders');
});

// Staff Management Routes (Role-based access)
Route::prefix('gestion')->middleware(['auth', 'role:admin,warehouse,cashier'])->name('staff.')->group(function () {
    Route::get('/', function () {
        return view('staff.dashboard');
    })->name('dashboard');

    Route::get('/pedidos', function () {
        return 'Gestión de pedidos';
    })->name('orders.index');

    Route::middleware('role:admin,warehouse')->group(function () {
        Route::get('/inventario', function () {
            return 'Inventario';
        })->name('inventory.index');

        Route::get('/lotes', function () {
            return 'Lotes y caducidades';
        })->name('batches.index');
    });

    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/tpv', function () {
            return 'TPV Tienda';
        })->name('pos.create');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/productos', function () {
            return 'Gestión de productos';
        })->name('products.index');

        Route::get('/categorias', function () {
            return 'Gestión de categorías';
        })->name('categories.index');

        Route::get('/cupones', function () {
            return 'Gestión de cupones';
        })->name('coupons.index');

        Route::get('/usuarios', function () {
            return 'Gestión de usuarios';
        })->name('users.index');

        Route::get('/auditoria', function () {
            return 'Registro de auditoría';
        })->name('audit.index');
    });
});
