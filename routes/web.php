<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Auth/Login'))
    ->middleware('guest');

Route::get('/home', function () {
    return Inertia::render('HomePage');
})->middleware(['auth'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/categories/create', fn() => Inertia::render('Category/AddCategory'))->name('categories.create');
    Route::get('/products/create', fn() => Inertia::render('Product/AddProduct'))->name('products.create');
});

require __DIR__ . '/auth.php';
