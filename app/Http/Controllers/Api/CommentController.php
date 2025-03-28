<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{


    public function list(Request $request)
    {
        try {
            // **Base Query (Fetch Comments with Related Project & Task)**
            $comments = Comment::with(['project', 'task']);

            // **Filter by `project_id`**
            if (!empty($request->project_id)) {
                $comments->where('project_id', $request->project_id);
                // dd("hiiiii");
            }

            // **Filter by `task_id`**
            if (!empty($request->task_id)) {


                $comments->where('task_id', $request->task_id);
            }


            // **Sorting (Optional)**
            $comments->orderBy('id', 'desc');

            // **Fetch Results**
            $comments = $comments->get();
            //    dd(count($comments));
            // **Map Assigned User IDs to Names**
            foreach ($comments as $comment) {
                // Assuming 'assigned_ids' is a JSON array or serialized array of user IDs
                $assignedIds = json_decode($comment->assigned_ids, true) ?? [];

                // Default empty array for assigned names
                $comment->assigned_names = [];

                // Check if assignedIds is not empty
                if (!empty($assignedIds)) {
                    // Fetch users based on assigned IDs
                    $assignedUsers = User::whereIn('id', $assignedIds)
                        ->select('first_name', 'last_name')
                        ->get()
                        ->map(function ($user) {
                            return ['full_name' => trim($user->first_name . ' ' . $user->last_name)];
                        });

                    // Assign the array of objects to the specific comment
                    $comment->assigned_names = $assignedUsers;
                }
            }

            // dd($assignedIds);
            $count = $comments->count();

            // **Return Response**
            return response()->json([
                'success' => true,
                'count' => $count,
                'data'    => $comments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching comments',
                'error'   => $e->getMessage()
            ], 500);
        }
    }




    public function limitedUser(Request $request)
    {
        $project = Task::where('id', $request['task_id'])
            ->where('project_id', $request['project_id'])
            ->first();

        $teamMembers = array();

        if ($project != null && $project->team != null) {
            $teamLeaderIds = json_decode($project->team, true);
            $teamMembers = User::whereIn('id', $teamLeaderIds)->get(['id', 'name', 'first_name', 'last_name']);
        }
        $result['count'] = $teamMembers->count();
        // Add the count of team members to the result
        $result['members'] = $teamMembers;

        return response()->json($result);
    }







    public function CommentCreate(Request $request)
    {
        // ✅ Validation Rules
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            // 'task_id'    => 'required', // If task_id is required, uncomment this
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Create the comment
            $comments = Comment::create([
                'project_id'      => $request->project_id,
                'task_id'         => $request->task_id,
                'documents'       => is_array($request->documents) ? json_encode($request->documents) : $request->documents,
                'audio_recording' => is_array($request->audio_recording) ? json_encode($request->audio_recording) : $request->audio_recording,
                'rollback'        => $request->rollback,
                'assigned_ids'    => is_array($request->assigned_ids) ? json_encode($request->assigned_ids) : $request->assigned_ids,
                'comments'        => $request->comments,
            ]);

            // Initialize the NotificationService
            $notifyService = new NotificationService();

            // Get the assigned users from the request (these are the users to notify)
            $assignedIds = $request->input('assigned_ids'); // 'assigned_ids' is already an array

            // Validate the 'assigned_ids' data, make sure it's an array
            if (!is_array($assignedIds)) {
                return response()->json(['success' => false, 'message' => 'Invalid assigned_ids data'], 400);
            }

            // Construct the notification body and heading
            $body =   $request->comments;
            $heading = '';

            // Send notification to each assigned user
            foreach ($assignedIds as $assignedUserId) {
                // Ensure user exists before sending notification
                $user = User::find($assignedUserId);
                if ($user) {
                    $notifyService->createNotification(
                        $assignedUserId,    // user_id (assigned user ID)
                        $body,               // body message (comment content)
                        $heading            // heading (New Comment Added)
                    );
                }
            }

            // Return the success response with the created comment data
            return response()->json([
                'success' => true,
                'message' => 'Comment created successfully',
                'data'    => $comments
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating comment',
                'error'   => $e->getMessage()
            ], 500);
        }
    }






    public function destroy(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:comments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => null
            ], 422);
        }

        try {
            // Find and delete the comment
            $comment = Comment::findOrFail($request->id);
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
