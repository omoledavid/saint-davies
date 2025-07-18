<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarHireController;
use App\Http\Controllers\HotelBookingController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelRoomCategoryController;
use App\Http\Controllers\HotelRoomController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyUnitController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TenantUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/verify-email/{token}', [AuthController::class, 'VerifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    //subscription
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscription', [SubscriptionController::class, 'current']);

    //users
    Route::prefix('users')->group(function () {

        //User management
        Route::get('/profile', [UserController::class, 'getProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/profile/password', [UserController::class, 'updatePassword']);
        Route::put('/profile/image', [UserController::class, 'updateImage']);

        //Wishlist
        Route::post('/wishlist/add', [WishlistController::class, 'addToWishlist']);
        Route::post('/wishlist/remove', [WishlistController::class, 'removeFromWishlist']);
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggleWishlist']);
        Route::get('/wishlist', [WishlistController::class, 'getUserWishlist']);

        //Reviews
        Route::post('/reviews/add', [ReviewController::class, 'addReview']);
        Route::post('/reviews/delete', [ReviewController::class, 'deleteReview']);
        Route::post('/reviews/stats', [ReviewController::class, 'getReviewStats']);
        Route::post('/reviews/item', [ReviewController::class, 'getItemReviews']);
        Route::post('/reviews/user', [ReviewController::class, 'getUserReviews']);

        //Hotel bookings
        Route::post('/hotel-bookings', [HotelBookingController::class, 'store']);
        Route::get('/hotel-bookings', [HotelBookingController::class, 'myBookings']);
        Route::post('/hotel-bookings/pay', [HotelBookingController::class, 'pay']);

        //Car hires
        Route::post('/car-hires', [CarHireController::class, 'store']);
        Route::get('/car-hires', [CarHireController::class, 'myHires']);
        Route::post('/car-hires/pay', [CarHireController::class, 'pay']);
    });

    Route::prefix('manager')->group(function () {

        //tenant
        Route::get('/tenants', [TenantController::class, 'index']);
        Route::post('/tenants', [TenantController::class, 'store']);
        Route::get('/tenants/{id}', [TenantController::class, 'show']);
        Route::put('/tenants/{id}', [TenantController::class, 'update']);
        Route::delete('/tenants/{id}', [TenantController::class, 'destroy']);

        //maintenance requests
        Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index']);
        Route::post('/maintenance-requests/change-status', [MaintenanceRequestController::class, 'changeStatus']);

        //property
        Route::get('/properties', [PropertyController::class, 'index']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::get('/properties/{id}', [PropertyController::class, 'show']);
        Route::put('/properties/{id}', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
        Route::get('/properties/{id}/reviews', [PropertyController::class, 'getReviews']);

        //property images
        Route::post('/properties/{id}/images', [PropertyController::class, 'uploadImages']);
        Route::post('/properties/images/{fileId}', [PropertyController::class, 'updateImage']);
        Route::delete('/properties/images/{fileId}', [PropertyController::class, 'deleteImage']);
        Route::post('/properties/images/{fileId}/set-main', [PropertyController::class, 'setMainImage']);
        Route::get('/properties/{id}/images', [PropertyController::class, 'getImages']);
        Route::post('/properties/{id}/images/reorder', [PropertyController::class, 'reorderImages']);

        //property unit
        Route::get('/property-units', [PropertyUnitController::class, 'index']);
        Route::post('/property-units', [PropertyUnitController::class, 'store']);
        Route::put('/property-units/{id}', [PropertyUnitController::class, 'update']);
        Route::delete('/property-units/{id}', [PropertyUnitController::class, 'destroy']);

        //property unit images
        Route::post('/property-units/{id}/images', [PropertyUnitController::class, 'uploadImages']);
        Route::post('/property-units/images/{fileId}', [PropertyUnitController::class, 'updateImage']);
        Route::delete('/property-units/images/{fileId}', [PropertyUnitController::class, 'deleteImage']);
        Route::post('/property-units/images/{fileId}/set-main', [PropertyUnitController::class, 'setMainImage']);
        Route::get('/property-units/{id}/images', [PropertyUnitController::class, 'getImages']);
        Route::post('/property-units/{id}/images/reorder', [PropertyUnitController::class, 'reorderImages']);

        //Hotel
        Route::get('hotel/bookings', [HotelController::class, 'myBookings']);
        Route::apiResource('hotel', HotelController::class);

        //hotel images
        Route::post('/hotel/{id}/images', [HotelController::class, 'uploadImages']);
        Route::post('/hotel/images/{fileId}', [HotelController::class, 'updateImage']);
        Route::delete('/hotel/images/{fileId}', [HotelController::class, 'deleteImage']);
        Route::post('/hotel/images/{fileId}/set-main', [HotelController::class, 'setMainImage']);
        Route::post('/hotel/{id}/images/reorder', [HotelController::class, 'reorderImages']);

        //hotel room categories
        Route::apiResource('hotel-room-categories', HotelRoomCategoryController::class);

        //hotel rooms
        Route::apiResource('hotel-rooms', HotelRoomController::class);


        //cars
        Route::get('cars/hires', [CarController::class, 'myHires']);
        Route::apiResource('cars', CarController::class);

        //car images
        Route::post('/cars/{id}/images', [CarController::class, 'uploadImages']);
        Route::post('/cars/images/{fileId}', [CarController::class, 'updateImage']);
        Route::delete('/cars/images/{fileId}', [CarController::class, 'deleteImage']);
        Route::post('/cars/image/{fileId}/set-main', [CarController::class, 'setMainImage']);
        Route::post('/cars/{id}/images/reorder', [CarController::class, 'reorderImages']);
    });
});
Route::prefix('tenants')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [TenantAuthController::class, 'login']);
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/logout', [TenantAuthController::class, 'logout']);
        Route::get('/dashboard', [TenantUserController::class, 'dashboard']);
        Route::post('/request-maintenance', [TenantUserController::class, 'requestMaintenance']);
    });
});
Route::prefix('general')->group(function () {
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/properties', [PropertyController::class, 'allProperties']);
    Route::get('/cars', [CarController::class, 'allCars']);
    Route::get('/hotels', [HotelController::class, 'allHotels']);
});

//verify payment
Route::get('verify-payment/hotel-booking/{reference}', [VerificationController::class, 'hotelBooking']);
Route::get('verify-payment/car-hire/{reference}', [VerificationController::class, 'carHire']);
