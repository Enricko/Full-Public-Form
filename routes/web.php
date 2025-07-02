<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LoginController;

// Public routes (no authentication required)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Authentication routes
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [LoginController::class, 'register'])->name('register');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/auth/check', [LoginController::class, 'checkAuth'])->name('auth.check');

// Public API routes (accessible to both guests and authenticated users)
Route::get('/trending-hashtags', [HomeController::class, 'getTrendingHashtags'])->name('trending.hashtags');
Route::get('/suggested-users', [HomeController::class, 'getSuggestedUsers'])->name('suggested.users');

    Route::get('/comment', [CommentController::class, 'index'])->name('comment');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // User profile routes
    Route::get('/me', [LoginController::class, 'me'])->name('me');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/{userId}', [ProfileController::class, 'index'])->name('profile.user')->where('userId', '[0-9]+');
    Route::put('/profile', [ProfileController::class, 'editProfile'])->name('editProfile');

    Route::post('/profile/switch-tab', [ProfileController::class, 'switchTab'])->name('profile.switchTab');
    Route::post('/profile/{userId}/switch-tab', [ProfileController::class, 'switchTab'])->name('profile.user.switchTab')->where('userId', '[0-9]+');

    Route::get('/profile/load-more', [ProfileController::class, 'loadMore'])->name('profile.loadMore');
    Route::get('/profile/{userId}/load-more', [ProfileController::class, 'loadMore'])->name('profile.user.loadMore')->where('userId', '[0-9]+');

    Route::post('/profile/refresh', [ProfileController::class, 'refresh'])->name('profile.refresh');
    Route::post('/profile/{userId}/refresh', [ProfileController::class, 'refresh'])->name('profile.user.refresh')->where('userId', '[0-9]+');

    Route::post('/profile/{userId}/follow', [ProfileController::class, 'toggleFollow'])->name('profile.follow')->where('userId', '[0-9]+');

    Route::get('/users', [ProfileController::class, 'users'])->name('users');

    // Posts routes
    Route::get('/posts/load-more', [HomeController::class, 'loadMore'])->name('posts.load-more');
    Route::get('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh');
    Route::post('/posts/refresh', [HomeController::class, 'refresh'])->name('posts.refresh.post');

    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');

    Route::get('/tambah', [PostController::class, 'create'])->name('tambah');
    Route::post('/tambah', [PostController::class, 'store'])->name('tambah.store');

    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

    // Post interactions
    Route::post('/posts/{post}/like', [PostController::class, 'like'])->name('posts.like');
    Route::delete('/posts/{post}/like', [PostController::class, 'like']);
    Route::post('/posts/{post}/save', [PostController::class, 'save'])->name('posts.save');
    Route::delete('/posts/{post}/save', [PostController::class, 'save']);
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

    Route::post('/post/{post}/like', [PostController::class, 'like'])->name('post.like');
    Route::post('/post/{post}/save', [PostController::class, 'save'])->name('post.save');
    Route::post('/post/{post}/share', [PostController::class, 'share'])->name('post.share');

    // Social features (actions requiring authentication)
    Route::post('/hashtag/{hashtagId}/follow', [HomeController::class, 'toggleHashtagFollow'])->name('hashtag.follow')->where('hashtagId', '[0-9]+');
    Route::post('/user/{userId}/follow', [HomeController::class, 'toggleUserFollow'])->name('user.follow')->where('userId', '[0-9]+');

    // Search routes
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    Route::get('/search/load-more', [SearchController::class, 'loadMore'])->name('search.load-more');
    Route::post('/search/filter', [SearchController::class, 'index'])->name('search.filter');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Comments routes
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/comments/{comment}/like', [CommentController::class, 'like'])->name('comments.like');
    Route::delete('/comments/{comment}/like', [CommentController::class, 'like']);
    Route::get('/comments/load-more', [CommentController::class, 'loadMore'])->name('comments.load-more');
});