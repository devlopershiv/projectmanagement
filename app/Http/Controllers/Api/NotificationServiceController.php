<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationServiceController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user notifications.
     */
    public function list(Request $request)
    {
        try {
            // **Initialize Notification Query**
            $notifications = Notification::query();
        
            // **Filter by `entered_by_id` (Who created the notification)**
            if ($request->filled('entered_by_id')) {
                $notifications->where('enteredbyid', $request->entered_by_id);
            }
        
            // **Filter by `user_id` (Notification for specific user)**
            if ($request->filled('user_id')) {
                $notifications->where('user_id', $request->user_id);
            }
        
            // **Total Count (before pagination)**
            $count = $notifications->count();  // Get the total count for pagination
        
            // **Pagination Parameters**
            $limit = $request->input('limit', 20);  // Default to 20 if not provided
            $page = $request->input('page', 1);     // Default to 1 if not provided
        
            // Validate pagination parameters (Ensure they are positive integers)
            if ($limit < 1 || $page < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid pagination parameters. Ensure "limit" and "page" are positive numbers.',
                    'data'    => null
                ], 422);
            }
        
            // **Sorting (Default: Newest First)**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';  // Default to 'desc' if 0 or not provided
                $notifications->orderBy($request->sort_by, $sortOrder);
            } else {
                $notifications->orderBy('id', 'desc');  // Default sorting by ID, descending
            }
        
            // **Apply Pagination**
            $notifications = $notifications->skip($limit * ($page - 1))->take($limit)->get();
        
            // // **If No Notifications Found**
            // if ($notifications->isEmpty()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No notifications found matching the criteria.',
            //         'data'    => null
            //     ], 404);  // 404 to indicate no results found
            // }
        
            // **Log the total count (Not inside the loop)**
            Log::info('Fetched Notifications Count: ' . $notifications->count());
        
            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Notification list fetched successfully.',
                'total'   => $count,
                'data'    => $notifications
            ], 200);
        } catch (\Exception $e) {
            // **Handle Error**
            Log::error('Error fetching notifications: ' . $e->getMessage());  // Log the error message
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching notifications.',
                'error'   => $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }
    
    

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request)
    {
        // **Fetch Notifications**
        $notifications = Notification::where('user_id', auth()->id());
    
        
    
        // **Mark All Notifications as Read**
        $notifications->update(['is_mark_read' => 1]);
    
        return response()->json([
            'success' => true,
            'message' => 'Notification(s) marked as read'
        ]);
    }
    

    /**
     * Delete notification.
     */
    public function delete(Request $request)
    {
        // **Check if User is Authenticated**
        $userId = auth()->id();
        
        // **Start Notification Query**
        $notifications = Notification::where('user_id', $userId);
    
        // **Delete Notifications**
        $deletedCount = $notifications->delete();  // Delete matching notifications
    
        // **Return Response after Deleting**
        return response()->json([
            'success' => true,
            'message' => 'Notification(s) deleted successfully'
        ]);
    }
    
    
    
    
}
