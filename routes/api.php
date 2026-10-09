<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/webhooks/leads', [\App\Http\Controllers\Api\LeadWebhookController::class, 'handle'])->name('webhooks.leads');

Route::get('/webhooks/whatsapp', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handle'])->name('webhooks.whatsapp.handle');

Route::post('/webhooks/subscription', [\App\Http\Controllers\Api\SubscriptionWebhookController::class, 'handle'])->name('webhooks.subscription');
