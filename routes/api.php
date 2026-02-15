<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthApiKeyMiddleware;
use App\Http\Controllers\Integration\OccurrenceIntegrationController;
use App\Http\Controllers\Api\OccurrenceController;

Route::middleware([AuthApiKeyMiddleware::class])->group(function () {
    Route::get('/', function () {
        return 'welcome';
    });

    // API INTERNA
    Route::get('/occurrences', [OccurrenceController::class, 'listAllOccurences']);

    // INTEGRAÇÃO EXTERNA
    Route::post('/integrations/occurrences', [OccurrenceIntegrationController::class, 'store']);
});

