<?php

use App\Http\Controllers\Channel\ChannelController;
use App\Http\Controllers\Page\AboutController;
use App\Http\Controllers\Page\DashboardController;
use App\Http\Controllers\Page\ExploreController;
use App\Http\Controllers\Page\LocaleController;
use App\Http\Controllers\Page\PolicyController;
use App\Http\Controllers\Page\WelcomeController;
use App\Http\Controllers\Page\WatchHistoryController;
use App\Http\Controllers\Page\TermsController;
use App\Http\Controllers\Video\VideoController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    // Welcome
    Route::get('/', WelcomeController::class);

    // Lang
    Route::get('lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

    // Public pages
    Route::get('about', AboutController::class)->name('about');
    Route::get('policy', PolicyController::class)->name('policy');
    Route::get('terms', TermsController::class)->name('terms');
    Route::get('explore', ExploreController::class)->name('explore');
    Route::get('/channels/@{user}', [ChannelController::class, 'show'])->whereNumber('user')->name('channels.show');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/history', WatchHistoryController::class)->name('history.index');
    Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');

    // Notifications
    Route::prefix('/notifications')->group(function () {
        Route::get('/', [VideoController::class, 'notifications'])->name('notifications.index');
        Route::delete('/{notification}', [VideoController::class, 'destroyNotification'])->name('notifications.destroy');
    });

    Route::prefix('/videos')->group(function () {

        // Likes & Comments
        Route::post('/{video}/likes/toggle', [VideoController::class, 'toggleLike'])->name('videos.likes.toggle');
        Route::post('/{video}/comments', [VideoController::class, 'storeComment'])->name('videos.comments.store');

        // Reports
        Route::post('/{video}/report', [VideoController::class, 'report'])->name('videos.report');

        // Videos
        Route::get('/{video}/status', [VideoController::class, 'checkUploadStatus']);
        Route::get('/', [VideoController::class, 'index'])->name('videos.index');
        Route::get('/create', [VideoController::class, 'create'])->name('videos.create');

        // FFMPEG Uploading
        Route::post('/', [VideoController::class, 'store'])->name('videos.store');

        // Optimized Uploading
        Route::post('/optimized', [VideoController::class, 'optimizedStore'])->name('videos.optimizedStore');

        Route::get('/{video}', [VideoController::class, 'show'])->whereNumber('video')->name('videos.show');
        Route::put('/{video}', [VideoController::class, 'update'])->name('videos.update');
        Route::delete('/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    });
});

require __DIR__ . '/admin.php';
