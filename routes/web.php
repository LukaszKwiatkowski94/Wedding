<?php

use App\Http\Controllers\AdminSongSuggestionController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\SongSuggestionController;
use App\Http\Controllers\WeddingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeddingController::class, 'show'])->name('home');
Route::post('/songs', [SongSuggestionController::class, 'store'])->middleware('throttle:song-suggestions')->name('songs.store');
Route::get('/setup', [SetupController::class, 'create'])->name('setup.create');
Route::post('/setup', [SetupController::class, 'store'])->middleware('throttle:5,1')->name('setup.store');
Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
    Route::get('/admin/settings', [WeddingController::class, 'edit'])->name('settings.edit');
    Route::put('/admin/settings', [WeddingController::class, 'update'])->name('settings.update');
    Route::get('/admin/songs', [AdminSongSuggestionController::class, 'index'])->name('admin.songs.index');
    Route::get('/admin/songs/export', [AdminSongSuggestionController::class, 'export'])->name('admin.songs.export');
});
