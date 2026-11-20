<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Content\CountryController;
use App\Http\Controllers\Api\V1\Content\FaqController;
use App\Http\Controllers\Api\V1\Content\PostCategoryController;
use App\Http\Controllers\Api\V1\Content\PostCommentController;
use App\Http\Controllers\Api\V1\Content\PostController;
use App\Http\Controllers\Api\V1\Content\ServiceController;
use App\Http\Controllers\Api\V1\Content\SiteSettingsController;
use App\Http\Controllers\Api\V1\Content\SuccessStoryController;
use App\Http\Controllers\Api\V1\Content\TeamMemberController;
use App\Http\Controllers\Api\V1\Content\TestimonialController;
use App\Http\Controllers\Api\V1\Content\UniversityController;
use App\Http\Controllers\Api\V1\Forms\ContactMessageController;
use App\Http\Controllers\Api\V1\Forms\NewsletterSubscriptionController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('health', HealthController::class)->name('health');

// Public website content (read-only, cached)
Route::get('team-members', [TeamMemberController::class, 'index'])->name('team-members.index');
Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
Route::get('success-stories', [SuccessStoryController::class, 'index'])->name('success-stories.index');
Route::get('site-settings', SiteSettingsController::class)->name('site-settings');
Route::get('services', [ServiceController::class, 'index'])->name('services.index');
Route::get('services/{slug}', [ServiceController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('services.show');
Route::get('universities', [UniversityController::class, 'index'])->name('universities.index');
Route::get('countries', [CountryController::class, 'index'])->name('countries.index');
Route::get('countries/{slug}', [CountryController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('countries.show');
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::get('posts/{slug}', [PostController::class, 'show'])->where('slug', '[a-z0-9-]+')->name('posts.show');
Route::get('posts/{slug}/comments', [PostCommentController::class, 'index'])->where('slug', '[a-z0-9-]+')->name('posts.comments.index');
Route::get('post-categories', [PostCategoryController::class, 'index'])->name('post-categories.index');

// Public website forms (stricter rate limit)
Route::middleware('throttle:forms')->group(function () {
    Route::post('contact-messages', [ContactMessageController::class, 'store'])->name('contact-messages.store');
    Route::post('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store'])->name('newsletter-subscriptions.store');
    Route::post('posts/{slug}/comments', [PostCommentController::class, 'store'])->where('slug', '[a-z0-9-]+')->name('posts.comments.store');
});

// Authentication
Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('register', 'register')->name('register');
        Route::post('login', 'login')->name('login');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', 'me')->name('me');
        Route::post('logout', 'logout')->name('logout');
    });
});

// Authenticated application routes
Route::middleware('auth:sanctum')->group(function () {
    // Feature routes go here, e.g. Route::apiResource('products', ProductController::class);
});
