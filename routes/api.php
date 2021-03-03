<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
}); */

// USUARIOS
Route::post('/user/register', [App\Http\Controllers\UserController::class, 'register']);
Route::post('/user/login', [App\Http\Controllers\UserController::class, 'login']);
Route::put('/user/update', [App\Http\Controllers\UserController::class, 'update']);
Route::post('/user/upload', [App\Http\Controllers\UserController::class, 'upload'])
    ->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::get('/user/avatar/{filename}', [App\Http\Controllers\UserController::class, 'getImage']);
Route::get('user/{id}', [App\Http\Controllers\UserController::class, 'detail']);


// CATEGORIAS
Route::resource('category', CategoryController::class);

// POSTS
Route::resource('/post', App\Http\Controllers\PostController::class)
    ->middleware(\App\Http\Middleware\ApiAuthMiddleware::class, [
        'except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser'
        ]
    ]
);
Route::post('/post/upload', [App\Http\Controllers\PostController::class, 'upload']);
Route::get('post/image/{filename}', [App\Http\Controllers\PostController::class, 'getImage']);
Route::get('post/category/{id}', [App\Http\Controllers\PostController::class, 'getPostsByCategory']);
Route::get('post/user/{id}', [App\Http\Controllers\PostController::class, 'getPostsByUser']);
