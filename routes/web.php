<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. HANYA SATU ROUTE UNTUK HALAMAN DEPAN ('/')
Route::get('/', function () {
    return Inertia::render('Home', [ // Pakai Home saja sesuai desain kita kemarin
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'menus' => [
            [
                'id' => 4, 
                'name' => 'Kopi Hitam Tanpa Gula', 
                'weight' => 4, 
                'desc' => 'Kopi murni dengan proses ekstraksi standar. Prioritas rendah (Best Effort).',
            ],
            [
                'id' => 3, 
                'name' => 'Kopi Hitam Dengan Gula', 
                'weight' => 3, 
                'desc' => 'Kopi hitam dengan tambahan pemanis. Prioritas menengah bawah.',
            ],
            [
                'id' => 2, 
                'name' => 'Kopi Susu Tanpa Gula', 
                'weight' => 2, 
                'desc' => 'Kopi dengan campuran susu creamy. Prioritas menengah atas.',
            ],
            [
                'id' => 1, 
                'name' => 'Kopi Susu Dengan Gula', 
                'weight' => 1, 
                'desc' => 'Varian terlengkap dengan proses tambahan. Prioritas tertinggi (Gold).',
            ],
        ],
    ]);
});

// 2. ROUTE DASHBOARD (Halaman Utama setelah Login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. ROUTE UNTUK PROSES PESAN
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('auth')
    ->name('order.store');

// 4. ROUTE PROFILE & LAINNYA
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/menu', function () {
    return Inertia::render('Menu');
});

Route::get('/about', function () {
    return Inertia::render('About');
});

require __DIR__.'/auth.php';