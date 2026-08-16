<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->middleware('role:admin')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('role:admin')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('role:admin')->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('role:admin')->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('role:admin')->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('role:admin')->name('users.destroy');

    foreach (['companies', 'customers', 'item-categories', 'units', 'items'] as $resource) {
        Route::get("/{$resource}", [MasterDataController::class, 'index'])->name("{$resource}.index");
        Route::get("/{$resource}/create", [MasterDataController::class, 'create'])->name("{$resource}.create");
        Route::post("/{$resource}", [MasterDataController::class, 'store'])->name("{$resource}.store");
        Route::get("/{$resource}/{id}/edit", [MasterDataController::class, 'edit'])->name("{$resource}.edit");
        Route::put("/{$resource}/{id}", [MasterDataController::class, 'update'])->name("{$resource}.update");
        Route::delete("/{$resource}/{id}", [MasterDataController::class, 'destroy'])->name("{$resource}.destroy");
    }

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->middleware('role:admin,staff')->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->middleware('role:admin,staff')->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->middleware('role:admin,staff')->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->middleware('role:admin,staff')->name('transactions.update');
    Route::post('/transactions/{transaction}/paid', [TransactionController::class, 'markPaid'])->middleware('role:admin,staff')->name('transactions.paid');
    Route::get('/transactions/{transaction}/{type}/preview', [TransactionController::class, 'document'])->name('transactions.preview');
    Route::get('/transactions/{transaction}/{type}/print', [TransactionController::class, 'document'])->name('transactions.print');

    Route::get('/document-templates', [DocumentTemplateController::class, 'index'])->middleware('role:admin')->name('document-templates.index');
    Route::get('/document-templates/create', [DocumentTemplateController::class, 'create'])->middleware('role:admin')->name('document-templates.create');
    Route::post('/document-templates', [DocumentTemplateController::class, 'store'])->middleware('role:admin')->name('document-templates.store');
    Route::get('/document-templates/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->middleware('role:admin')->name('document-templates.edit');
    Route::put('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'update'])->middleware('role:admin')->name('document-templates.update');
    Route::delete('/document-templates/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->middleware('role:admin')->name('document-templates.destroy');
});
