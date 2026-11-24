<?php

use App\Http\Controllers\Api\V1\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Api\V1\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Api\V1\Admin\PostCategoryController as AdminPostCategoryController;
use App\Http\Controllers\Api\V1\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\V1\Admin\TagController as AdminTagController;
use App\Http\Controllers\Api\V1\Admin\TeamMemberController as AdminTeamMemberController;
use App\Http\Controllers\Api\V1\Admin\TestimonialController as AdminTestimonialController;
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

    // Public "who am I": the signed-in user, or null for guests (not a 401, so visiting the
    // site while logged out never logs an error in the browser console).
    Route::get('me', 'me')->name('me');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout')->name('logout');
    });
});

// Admin CMS panel
Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('posts', AdminPostController::class);
    Route::apiResource('team-members', AdminTeamMemberController::class);
    Route::apiResource('testimonials', AdminTestimonialController::class);
    Route::apiResource('post-categories', AdminPostCategoryController::class)->except(['show']);
    Route::apiResource('tags', AdminTagController::class)->except(['show']);

    Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
    Route::patch('comments/{comment}', [AdminCommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::patch('contact-messages/{message}', [AdminContactMessageController::class, 'update'])->name('contact-messages.update');
    Route::delete('contact-messages/{message}', [AdminContactMessageController::class, 'destroy'])->name('contact-messages.destroy');

    Route::get('newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
    Route::delete('newsletter-subscribers/{subscriber}', [AdminNewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
});
