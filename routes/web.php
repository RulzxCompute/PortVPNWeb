<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NodeController as AdminNodeController;
use App\Http\Controllers\Admin\RedeemCodeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\PortController as UserPortController;
use App\Http\Controllers\User\RedeemController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\TransactionController as UserTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// User Routes
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Ports
    Route::get('/ports', [UserPortController::class, 'index'])->name('ports.index');
    Route::get('/ports/create', [UserPortController::class, 'create'])->name('ports.create');
    Route::post('/ports', [UserPortController::class, 'store'])->name('ports.store');
    Route::get('/ports/{port}', [UserPortController::class, 'show'])->name('ports.show');
    Route::get('/ports/{port}/download/{type}', [UserPortController::class, 'downloadConfig'])->name('ports.download');
    
    // Redeem
    Route::get('/redeem', [RedeemController::class, 'showForm'])->name('redeem');
    Route::post('/redeem', [RedeemController::class, 'redeem'])->name('redeem.submit');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Transactions
    Route::get('/transactions', [UserTransactionController::class, 'index'])->name('transactions.index');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Nodes
    Route::get('/nodes', [AdminNodeController::class, 'index'])->name('nodes.index');
    Route::get('/nodes/create', [AdminNodeController::class, 'create'])->name('nodes.create');
    Route::post('/nodes', [AdminNodeController::class, 'store'])->name('nodes.store');
    Route::get('/nodes/{node}/edit', [AdminNodeController::class, 'edit'])->name('nodes.edit');
    Route::put('/nodes/{node}', [AdminNodeController::class, 'update'])->name('nodes.update');
    Route::delete('/nodes/{node}', [AdminNodeController::class, 'destroy'])->name('nodes.destroy');
    Route::post('/nodes/{node}/regenerate-key', [AdminNodeController::class, 'regenerateApiKey'])->name('nodes.regenerate-key');
    Route::post('/nodes/{node}/ping', [AdminNodeController::class, 'testPing'])->name('nodes.ping');
    
    // Redeem Codes
    Route::get('/redeem-codes', [RedeemCodeController::class, 'index'])->name('redeem-codes.index');
    Route::get('/redeem-codes/create', [RedeemCodeController::class, 'create'])->name('redeem-codes.create');
    Route::post('/redeem-codes', [RedeemCodeController::class, 'store'])->name('redeem-codes.store');
    Route::delete('/redeem-codes/{redeemCode}', [RedeemCodeController::class, 'destroy'])->name('redeem-codes.destroy');
    Route::get('/redeem-codes/export', [RedeemCodeController::class, 'export'])->name('redeem-codes.export');
    
    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/add-balance', [AdminUserController::class, 'addBalance'])->name('users.add-balance');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    
    // Ports
    Route::get('/ports', [AdminPortController::class, 'index'])->name('ports.index');
    Route::get('/ports/{port}', [AdminPortController::class, 'show'])->name('ports.show');
    Route::post('/ports/{port}/suspend', [AdminPortController::class, 'suspend'])->name('ports.suspend');
    Route::post('/ports/{port}/activate', [AdminPortController::class, 'activate'])->name('ports.activate');
    Route::delete('/ports/{port}', [AdminPortController::class, 'destroy'])->name('ports.destroy');
    
    // Transactions
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
});
