<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/t/open/{token}', [\App\Http\Controllers\Tracking\EmailOpenTrackerController::class, 'track'])->name('tracking.open');
Route::get('/t/click/{token}', [\App\Http\Controllers\Tracking\LinkClickTrackerController::class, 'redirect'])->name('tracking.click');
Route::get('/unsubscribe/{token}', [\App\Http\Controllers\Tracking\UnsubscribeController::class, 'unsubscribe'])->name('tracking.unsubscribe');
