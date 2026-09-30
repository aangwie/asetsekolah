<?php

use App\Http\Controllers\AssetTransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : view('welcome'))->name('home');
Route::get('/preview', fn () => view('welcome'))->name('preview');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions/asset/masuk', [AssetTransactionController::class, 'indexMasuk'])->name('transactions.asset.masuk');
    Route::get('/transactions/asset/masuk/create', [AssetTransactionController::class, 'createMasuk'])->name('transactions.asset.masuk.create');
    Route::post('/transactions/asset/masuk', [AssetTransactionController::class, 'storeMasuk'])->name('transactions.asset.masuk.store');
    Route::get('/transactions/asset/keluar', fn () => view('transactions.placeholder', ['label' => 'Aset Keluar']))->name('transactions.asset.keluar');
    Route::get('/transactions/bhp/masuk', fn () => view('transactions.placeholder', ['label' => 'BHP Masuk']))->name('transactions.bhp.masuk');
    Route::get('/transactions/bhp/keluar', fn () => view('transactions.placeholder', ['label' => 'BHP Keluar']))->name('transactions.bhp.keluar');
    Route::resource('locations', LocationController::class)->except('show');
    Route::resource('buildings', BuildingController::class)->except('show');
    Route::resource('users', UserController::class)->except('show')->middleware('role:superadmin');
});

