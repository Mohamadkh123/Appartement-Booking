<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ApartmentController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\WalletController;
use App\Http\Controllers\API\FcmTokenController;


Route::post('/register', [RegisterController::class, 'register']); 
Route::post('/login', [RegisterController::class, 'login']); 

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [RegisterController::class, 'profile']); 
    Route::put('/user', [RegisterController::class, 'updateProfile']); 
    Route::post('/logout', [RegisterController::class, 'logout']); 

    Route::post('/store-fcm-token', [FcmTokenController::class, 'store']);
    Route::get('/get-fcm-token/{userId}', [FcmTokenController::class, 'getToken']);

    Route::get('/wallet/balance/{user}', [WalletController::class, 'balance']); 

    Route::get('/apartments', [ApartmentController::class, 'index']); 
    Route::get('/apartments/{apartment}', [ApartmentController::class, 'show']);
    Route::get('/apartments/{apartment}/reviews', [ReviewController::class, 'apartmentReviews']); 

    Route::post('/apartments', [ApartmentController::class, 'store']); 
    Route::post('/apartments/{apartment}', [ApartmentController::class, 'update']); 
    Route::delete('/apartments/{apartment}', [ApartmentController::class, 'destroy']); 

    Route::post('/apartments/{apartment}/favorite', [ApartmentController::class, 'addToFavorites']); 
    Route::delete('/apartments/{apartment}/favorite', [ApartmentController::class, 'removeFromFavorites']);
    Route::get('/favorites', [ApartmentController::class, 'favorites']); 
    Route::post('/favorites/{apartment}/book', [ApartmentController::class, 'bookFromFavorites']); 

    Route::post('/bookings', [BookingController::class, 'store']); 
    Route::get('/bookings', [BookingController::class, 'index']); 
    Route::get('/bookings/{booking}', [BookingController::class, 'show']); 
    Route::put('/bookings/{booking}', [BookingController::class, 'update']); 
    Route::put('/bookings/{booking}/details', [BookingController::class, 'updateDetails']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); 
    Route::get('/my-bookings', [BookingController::class, 'myBookings']); 

    Route::post('/reviews', [ReviewController::class, 'store']); 
    Route::get('/reviews', [ReviewController::class, 'index']); 



});
