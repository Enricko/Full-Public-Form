<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutController;

// Home routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Profile routes (no auth required for testing with user ID 6)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/profile/{userId}', [ProfileController::class, 'index'])->name('profile.user')->where('userId', '[0-9]+');
Route::put('/profile', [ProfileController::class, 'editProfile'])->name('editProfile');

// Profile AJAX routes with user ID support
Route::post('/profile/switch-tab', [ProfileController::class, 'switchTab'])->name('profile.switchTab');
Route::post('/profile/{userId}/switch-tab', [ProfileController::class, 'switchTab'])->name('profile.user.switchTab')->where('userId', '[0-9]+');

Route::get('/profile/load-more', [ProfileController::class, 'loadMore'])->name('profile.loadMore');
Route::get('/profile/{userId}/load-more', [ProfileController::class, 'loadMore'])->name('profile.user.loadMore')->where('userId', '[0-9]+');

Route::post('/profile/refresh', [ProfileController::class, 'refresh'])->name('profile.refresh');
Route::post('/profile/{userId}/refresh', [ProfileController::class, 'refresh'])->name('profile.user.refresh')->where('userId', '[0-9]+');

// Follow/Unfollow routes
Route::post('/profile/{userId}/follow', [ProfileController::class, 'toggleFollow'])->name('profile.follow')->where('userId', '[0-9]+');

// Users listing route
Route::get('/users', [ProfileController::class, 'users'])->name('users');

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


// Hashtag and User follow routes for sidebar
// Sidebar data endpoints
Route::get('/trending-hashtags', [HomeController::class, 'getTrendingHashtags'])->name('trending.hashtags');
Route::get('/suggested-users', [HomeController::class, 'getSuggestedUsers'])->name('suggested.users');

// Follow functionality
Route::post('/hashtag/{hashtagId}/follow', [HomeController::class, 'toggleHashtagFollow'])->name('hashtag.follow')->where('hashtagId', '[0-9]+');
Route::post('/user/{userId}/follow', [HomeController::class, 'toggleUserFollow'])->name('user.follow')->where('userId', '[0-9]+');