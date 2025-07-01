<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;

// Home routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Profile routes (no auth required for testing with user ID 6)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::put('/profile', [ProfileController::class, 'editProfile'])->name('editProfile');

// IMPORTANT: Add these missing profile routes
Route::post('/profile/switch-tab', [ProfileController::class, 'switchTab'])->name('profile.switchTab');
Route::get('/profile/load-more', [ProfileController::class, 'loadMore'])->name('profile.loadMore');
Route::post('/profile/refresh', [ProfileController::class, 'refresh'])->name('profile.refresh');

// About route
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Home page infinite scroll routes
Route::get('/posts/load-more', [HomeController::class, 'loadMore'])->name('posts.load-more');
Route::get('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh');
Route::post('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh.post');

// Post creation routes
Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

// Alternative routes for your existing form
Route::get('/tambah', [PostController::class, 'create'])->name('tambah');
Route::post('/tambah', [PostController::class, 'store'])->name('tambah.store');

// Post routes
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// Post interactions (REMOVE auth middleware for testing with user ID 6)
Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
Route::delete('/posts/{post}/like', [PostController::class, 'like']);
Route::post('/posts/{post}/save', [PostController::class, 'save'])->name('posts.save');
Route::delete('/posts/{post}/save', [PostController::class, 'save']);
Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

// Alternative post routes (for compatibility)
Route::post('/post/{post}/like', [PostController::class, 'like'])->name('post.like');
Route::post('/post/{post}/save', [PostController::class, 'save'])->name('post.save');
Route::post('/post/{post}/share', [PostController::class, 'share'])->name('post.share');

// Search routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/load-more', [SearchController::class, 'loadMore'])->name('search.load-more');

// Additional search API routes for AJAX functionality
Route::post('/search/filter', [SearchController::class, 'index'])->name('search.filter');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');  

// Comment routes
Route::get('/comment', [CommentController::class, 'index'])->name('comment');
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
Route::post('/comments/{comment}/like', [CommentController::class, 'like'])->name('comments.like');
Route::delete('/comments/{comment}/like', [CommentController::class, 'like']);

// Load more comments
Route::get('/comments/load-more', [CommentController::class, 'loadMore'])->name('comments.load-more');