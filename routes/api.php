<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CountController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\NotificationServiceController;
use App\Http\Controllers\Api\CampaignsController;
use App\Http\Controllers\Api\CollaborationController;
use App\Http\Controllers\Api\SeoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// 🔹 Public Routes
Route::post('/login', [AuthController::class, 'login']);

// 🔹 Protected Routes (Require Auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::get('/user', [AuthController::class, 'user']);

    //Users
    Route::post('users/create/update', [UsersController::class, 'userCreateUpdate']);
    Route::post('users/list', [UsersController::class, 'list']);
    Route::post('users/details', [UsersController::class, 'details']);
    Route::post('users_status', [UsersController::class, 'users_status']);
    Route::post('user/getloggedinuser', [UsersController::class, 'getuser']);
   

     //project
     Route::post('project/create', [ProjectController::class, 'store']);
     Route::post('project/list', [ProjectController::class, 'list']);
     Route::post('project/details', [ProjectController::class, 'details']);
     Route::post('project/update', [ProjectController::class, 'store']);
     Route::post('project_status', [ProjectController::class, 'project_status']);
     Route::post('project/status', [ProjectController::class, 'status']);
     Route::post('project/priority', [ProjectController::class, 'Priority_status']);

      //Tasks
      Route::post('task/create', [TaskController::class, 'store']);
      Route::post('task/list', [TaskController::class, 'list']);
      Route::post('task/details', [TaskController::class, 'details']);
      Route::post('task/update', [TaskController::class, 'store']);
      Route::post('task_status', [TaskController::class, 'task_status']);
      Route::post('task/status', [TaskController::class, 'status']);
      Route::post('task/priority', [TaskController::class, 'Priority_status']);

     //dashboard
    Route::post('dashboard', [CountController::class, 'dashboard']);

     //comments 
     Route::post('comment/create', [CommentController::class, 'CommentCreate']);
     Route::post('comment/list', [CommentController::class, 'list']);
     Route::post('comment/destroy', [CommentController::class, 'destroy']);
     Route::post('comment/users', [CommentController::class, 'limitedUser']);

      //notifications 
     Route::post('notification/read', [NotificationServiceController ::class, 'markAsRead']);
     Route::post('notification/list', [NotificationServiceController ::class, 'list']);
     Route::post('notification/delete', [NotificationServiceController ::class, 'delete']);

    
     //post
     Route::post('post/create/update', [PostController::class, 'CreatePostUpdate']);
     Route::post('post/list', [PostController::class, 'list']);
     Route::post('post/details', [PostController::class, 'details']);
     Route::post('post/status', [PostController::class, 'post_status']);
     Route::post('post/approvalstatus', [PostController::class, 'PostApprovalStatus']);

     //campaigns
     Route::post('campaigns/create/update', [CampaignsController::class, 'CreateCampaignsUpdate']);
     Route::post('campaigns/list', [CampaignsController::class, 'list']);
     Route::post('campaigns/details', [CampaignsController::class, 'details']);
     Route::post('campaigns_status', [CampaignsController::class, 'Campaigns_status']);
     
     
      //SEo
      Route::post('seo/create/update', [SeoController::class, 'CreateSeoUpdate']);
      Route::post('seo/list', [SeoController::class, 'list']);
      Route::post('seo/details', [SeoController::class, 'details']);
      Route::post('seo_status', [SeoController::class, 'Seo_status']);
      Route::post('seo_priority_status', [SeoController::class, 'Priority_status']);


       //Collaboration
     Route::post('collaboration/create/update', [CollaborationController::class, 'CreateSeoUpdate']);
     Route::post('collaboration/list', [CollaborationController::class, 'list']);
     Route::post('collaboration/details', [CollaborationController::class, 'details']);
     Route::post('payment_status', [CollaborationController::class, 'Seo_status']);
});




