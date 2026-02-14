<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// ROOT: redirect login jika belum auth
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
})->name('root');

// Admin Login (guest only)
Route::middleware('guest')->group(function () {
    // Ganti /admin/login -> /admin0
    Route::get('/admin0', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin0', [AdminLoginController::class, 'store']);
});


// Admin (auth + admin, prefix admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardAdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');
});


// User/Kasir (auth + verified) - both roles can access
Route::middleware(['auth', 'verified'])->group(function () {
    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Categories
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    // Products
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/items', [CartController::class, 'addItem'])->name('cart.items.store');
    Route::post('/cart/store', [CartController::class, 'addItem'])->name('cart.store');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/items/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.items.update');
    Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem'])->name('cart.items.destroy');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/payment-success/{transaction}', [CartController::class, 'paymentSuccess'])->name('payment.success');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
