<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServicesController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\StaticPagesController;
use App\Http\Controllers\Api\DoctorsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
     
Route::middleware('auth:sanctum')->group( function () {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::resource('services', ServicesController::class);
    Route::post('services_status', [ServicesController::class,'changeServiceStatus']);
    Route::get('dashboard', [ServicesController::class,'getDashboardData']);
    Route::resource('latestnews', CommonController::class);
    Route::post('news_status', [CommonController::class,'changeLatestNewsStatus']);
    Route::post('add_gallery_img', [CommonController::class,'addGalleryImage']);
    Route::post('change_status_gallery', [CommonController::class,'changeStatusGalleryImg']);
    Route::post('delete_gallery_img', [CommonController::class,'deleteGalleryImg']);
    Route::get('appointments/{appointment_id?}', [CommonController::class,'getAppointments']);
    Route::post('review_status', [ServicesController::class,'changeReviewStatus']);
    Route::get('reviews', [ServicesController::class,'getReviewsDashboard']);
    Route::get('reviews/{review_id}', [ServicesController::class,'getReviewsDetail']);
    Route::post('reviews', [ServicesController::class,'saveReviewsDashboard']);
    Route::post('update_review/{review_id}', [ServicesController::class,'updateReviewsDashboard']);
    Route::post('delete', [ServicesController::class,'deleteReview']);
    Route::resource('static_pages', StaticPagesController::class);
    Route::resource('doctors', DoctorsController::class);
    Route::post('change_doctor_status', [DoctorsController::class,'changeDoctorStatus']);
});

Route::get('get_services/{id?}', [ServicesController::class,'getServicesList']);
Route::get('get_latest_news/{id?}', [CommonController::class,'getLatestNews']);
Route::get('gallery_list', [CommonController::class,'getGalleryList']);
Route::post('book_appointment', [CommonController::class,'bookAnAppointment']);
Route::get('reviews_front', [ServicesController::class,'getReviewsFrontend']);
Route::get('static_pages_front/{page_id?}', [StaticPagesController::class,'getPagesFrontend']);
Route::get('doctors_front', [DoctorsController::class,'getDoctorsFrontend']);

