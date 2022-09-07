<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{AuthController,
    CategoryController,
    DashboardController,
    ResourceController,
    ResourceVoteController,
    ReviewController,
    UserController};

Route::group([
    'as' => 'admin.auth.',
    'controller' => AuthController::class,
], function () {
    Route::post('login', 'login')
        ->name('login');

    Route::group([
        'middleware' => ['auth:sanctum', 'verified', 'is_admin']
    ], function () {
        Route::post('logout', 'logout')
            ->name('logout');
    });
});

Route::middleware(['auth:sanctum', 'verified', 'is_admin'])->group(function () {
    Route::group([
        'as' => 'admin.dashboard.',
        'controller' => DashboardController::class,
    ], function () {
        Route::get('dashboard', 'index')
            ->name('index');
    });

    Route::group([
        'as' => 'admin.',
        'controller' => UserController::class,
    ], function () {
        Route::delete('users/{user}/avatar', 'deleteAvatar')
            ->name('users.delete.avatar');

        Route::apiResource('users', UserController::class);
    });


    Route::group([
        'as' => 'admin.',
        'controller' => ResourceController::class,
    ], function () {
        Route::get('resources/{resource}/votes', 'votes')
            ->name('resources.votes');

        Route::get('resources/{resource}/reviews', 'reviews')
            ->name('resources.reviews');

        Route::apiResource('resources', ResourceController::class);
    });

    Route::apiResource(
        'resources/votes',
        ResourceVoteController::class,
        ['as' => 'admin']
    );

    Route::apiResource(
        'reviews',
        ReviewController::class,
        ['as' => 'admin']
    );

    Route::apiResource(
        'categories',
        CategoryController::class,
        ['as' => 'admin']
    );

});