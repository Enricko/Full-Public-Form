<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;

Route::get('/', [HomeController::class, 'index'])->name('home');
// Alternative endpoints for infinite scroll (optional)
Route::get('/posts/load-more', [HomeController::class, 'loadMore'])->name('posts.load-more');
Route::get('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh');

// If you want to add pull-to-refresh functionality
Route::post('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh.post');


Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::delete('/posts/{post}/like', [PostController::class, 'like']);
    Route::post('/posts/{post}/save', [PostController::class, 'save'])->name('posts.save');
    Route::delete('/posts/{post}/save', [PostController::class, 'save']);
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');
});
