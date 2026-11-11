<?php

use App\Http\Controllers\Api\V1\StatusController;
use Illuminate\Support\Facades\Route;

/*
| Public API for the Next.js site. Everything is versioned under /api/v1.
| Document each endpoint in docs/api.md.
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('status', StatusController::class)->name('status');
});
