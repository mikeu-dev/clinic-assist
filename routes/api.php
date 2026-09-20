<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhook/whatsapp')->group(function (): void {
    Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
    Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');
});
