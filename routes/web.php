<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\PublicPortal;
use Illuminate\Support\Facades\Route;

// ===== Portal publik (tanpa login) =====

Route::get('/', [PublicPortal\HomeController::class, 'index'])->name('home');

Route::get('knowledge-base', [PublicPortal\KnowledgeBaseController::class, 'index'])->name('kb.index');
Route::get('knowledge-base/{article:slug}', [PublicPortal\KnowledgeBaseController::class, 'show'])->name('kb.show');

Route::get('tickets/create', [PublicPortal\TicketController::class, 'create'])->name('tickets.create');
Route::post('tickets', [PublicPortal\TicketController::class, 'store'])->middleware('throttle:tickets')->name('tickets.store');
Route::get('ticket-created', [PublicPortal\TicketController::class, 'success'])->name('tickets.success');
Route::get('track', [PublicPortal\TicketController::class, 'track'])->name('tickets.track');
Route::post('track', [PublicPortal\TicketController::class, 'trackResult'])->middleware('throttle:tickets')->name('tickets.track.result');

// ===== Portal administrator (login) =====

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('tickets', [Admin\TicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/{ticket}', [Admin\TicketController::class, 'show'])->name('tickets.show');
    Route::patch('tickets/{ticket}', [Admin\TicketController::class, 'update'])->name('tickets.update');
    Route::post('tickets/{ticket}/transition', [Admin\TicketController::class, 'transition'])->name('tickets.transition');

    Route::get('master/{entity}', [Admin\MasterDataController::class, 'index'])->name('master.index');
    Route::post('master/{entity}', [Admin\MasterDataController::class, 'store'])->name('master.store');
    Route::put('master/{entity}/{itemId}', [Admin\MasterDataController::class, 'update'])->name('master.update');
    Route::delete('master/{entity}/{itemId}', [Admin\MasterDataController::class, 'destroy'])->name('master.destroy');

    Route::get('knowledge-base', [Admin\ArticleController::class, 'index'])->name('kb.index');
    Route::get('knowledge-base/create', [Admin\ArticleController::class, 'create'])->name('kb.create');
    Route::post('knowledge-base', [Admin\ArticleController::class, 'store'])->name('kb.store');
    Route::get('knowledge-base/{article}/edit', [Admin\ArticleController::class, 'edit'])->name('kb.edit');
    Route::put('knowledge-base/{article}', [Admin\ArticleController::class, 'update'])->name('kb.update');
    Route::delete('knowledge-base/{article}', [Admin\ArticleController::class, 'destroy'])->name('kb.destroy');

    Route::get('audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit.index');
});

require __DIR__.'/settings.php';
