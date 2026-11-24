<?php

use App\Http\Controllers\AdminGameSuggestionController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminSongSuggestionController;
use App\Http\Controllers\AdminTimelineEventController;
use App\Http\Controllers\GameSuggestionController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\SongSuggestionController;
use App\Http\Controllers\WeddingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WeddingController::class, 'show'])->name('home');
Route::post('/songs', [SongSuggestionController::class, 'store'])->middleware('throttle:song-suggestions')->name('songs.store');
Route::post('/games', [GameSuggestionController::class, 'store'])->middleware('throttle:game-suggestions')->name('games.store');
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
    Route::get('/admin/games', [AdminGameSuggestionController::class, 'index'])->name('admin.games.index');
    Route::patch('/admin/games/{game}/publish', [AdminGameSuggestionController::class, 'publish'])->whereNumber('game')->name('admin.games.publish');
    Route::patch('/admin/games/{game}/unpublish', [AdminGameSuggestionController::class, 'unpublish'])->whereNumber('game')->name('admin.games.unpublish');
    Route::get('/admin/timeline', [AdminTimelineEventController::class, 'index'])->name('admin.timeline.index');
    Route::get('/admin/timeline/create', [AdminTimelineEventController::class, 'create'])->name('admin.timeline.create');
    Route::post('/admin/timeline', [AdminTimelineEventController::class, 'store'])->name('admin.timeline.store');
    Route::get('/admin/timeline/{event}/edit', [AdminTimelineEventController::class, 'edit'])->whereNumber('event')->name('admin.timeline.edit');
    Route::put('/admin/timeline/{event}', [AdminTimelineEventController::class, 'update'])->whereNumber('event')->name('admin.timeline.update');
    Route::get('/admin/timeline/{event}/delete', [AdminTimelineEventController::class, 'confirmDelete'])->whereNumber('event')->name('admin.timeline.delete');
    Route::delete('/admin/timeline/{event}', [AdminTimelineEventController::class, 'destroy'])->whereNumber('event')->name('admin.timeline.destroy');
    Route::get('/admin/menu', [AdminMenuController::class, 'index'])->name('admin.menu.index');
    Route::post('/admin/menu/categories', [AdminMenuController::class, 'storeCategory'])->name('admin.menu.categories.store');
    Route::put('/admin/menu/categories/{category}', [AdminMenuController::class, 'updateCategory'])->whereNumber('category')->name('admin.menu.categories.update');
    Route::delete('/admin/menu/categories/{category}', [AdminMenuController::class, 'destroyCategory'])->whereNumber('category')->name('admin.menu.categories.destroy');
    Route::patch('/admin/menu/categories/{category}/{direction}', [AdminMenuController::class, 'moveCategory'])->whereNumber('category')->whereIn('direction', ['up', 'down'])->name('admin.menu.categories.move');
    Route::post('/admin/menu/items', [AdminMenuController::class, 'storeItem'])->name('admin.menu.items.store');
    Route::put('/admin/menu/items/{item}', [AdminMenuController::class, 'updateItem'])->whereNumber('item')->name('admin.menu.items.update');
    Route::delete('/admin/menu/items/{item}', [AdminMenuController::class, 'destroyItem'])->whereNumber('item')->name('admin.menu.items.destroy');
    Route::patch('/admin/menu/items/{item}/{direction}', [AdminMenuController::class, 'moveItem'])->whereNumber('item')->whereIn('direction', ['up', 'down'])->name('admin.menu.items.move');
});
