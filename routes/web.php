<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\YandexReviewsController;
use App\Http\Controllers\YandexSettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/yandex/settings', [YandexSettingsController::class, 'index'])->name('yandex.settings');
    Route::post('/yandex/settings', [YandexSettingsController::class, 'store'])->name('yandex.settings.store');
    Route::get('/yandex/reviews', [YandexReviewsController::class, 'index'])->name('yandex.reviews');
    Route::post('/yandex/reviews/refresh', [YandexReviewsController::class, 'refresh'])->name('yandex.reviews.refresh');
});

require __DIR__.'/auth.php';
