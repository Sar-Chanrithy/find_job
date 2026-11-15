<?php

use App\Http\Controllers\Api\JobController;
use Illuminate\Support\Facades\Route;

// Public browsing only. Mutations will require authentication and ownership policies.
Route::middleware('throttle:public-api')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{id}', [JobController::class, 'show'])->whereNumber('id');
});
