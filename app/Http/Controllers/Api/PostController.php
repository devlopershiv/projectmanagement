<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;

class PostController extends Controller
{


    public function CreatePostUpdate(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'brand_name'   => 'required',
                'post_caption' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // **Check if ID exists (Update case)**
            if (!empty($request->id)) {
                $post = Post::find($request->id);

                if (!$post) {
                    return response()->json(['message' => 'Post not found.'], 404);
                }
            } else {
                $post = new Post(); // **Create New Post Instance**
            }

            // **Set Fields (Keeps Old Values If Not Provided)**
            $post->brand_name     = $request->brand_name ?? $post->brand_name;
            $post->platform       = $request->platform ?? $post->platform;
            $post->post_type      = $request->post_type ?? $post->post_type;
            $post->post_caption   = $request->post_caption ?? $post->post_caption;
            $post->post_url       = $request->post_url ?? $post->post_url;
            $post->media_upload   = $request->media_upload ?? $post->media_upload;
            $post->team           = $request->team ?? $post->team;
            $post->description    = $request->description ?? $post->description;
            $post->post_status    = $request->post_status ?? 0; // Default post_status = 0
            $post->scheduled_date = $request->scheduled_date ?? $post->scheduled_date;
            $post->active_status  = $request->active_status ?? 1;

            $post->save(); // **Save Post**

            // **Assign Auto-Generated Post ID (Only for New Posts)**
            if (empty($post->post_id)) {
                $post->post_id = 'POST-' . str_pad($post->id, 6, '0', STR_PAD_LEFT);
                $post->save(); // **Save Again After Setting post_id**
            }
            $message = !empty($request->id) ? 'Post updated successfully.' : 'Post created successfully.';

            // **Return JSON response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'post'    => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while processing the request.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function list(Request $request)
    {
        try {
            // **Base Query (Fetch Posts)**
            $posts = Post::query();

            // **Search Filter (Search by brand_name or post_caption)**
            if (!empty($request->search)) {
                $search = $request->search;
                $posts->where(function ($query) use ($search) {
                    $query->where('brand_name', 'LIKE', "%{$search}%")
                        ->orWhere('post_caption', 'LIKE', "%{$search}%");
                });
            }

            // **Filtering based on 'post_status'**
            if ($request->has('post_status') && in_array($request->post_status, [0, 1])) {
                $posts->where('post_status', $request->post_status);
            }

            // **Filter by `team` (if provided)**
            if ($request->filled('team')) {
                $team = $request->team;
                $posts->whereRaw("JSON_CONTAINS(team, '\"$team\"')"); // MySQL JSON Query
            }

            // **Total Count**
            $count = $posts->count();

            // **Pagination Parameters**
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            // **Sorting**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $posts->orderBy($request->sort_by, $sortOrder);
            } else {
                $posts->orderBy('id', 'desc');
            }

            // **Apply Pagination**
            $posts = $posts->skip($limit * ($page - 1))->take($limit)->get();

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Posts list fetched successfully.',
                'total'   => $count,
                'data'    => $posts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching posts.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function details(Request $request)
    {
        try {
            // **Step 1: Validate Request**
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data'    => null
                ], 422);
            }

            // **Step 2: Fetch Post Details**
            $post = Post::where('id', $request->id)->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                    'data'    => null
                ], 404);
            }

            // **Step 3: Fetch Team Members (if `team` is stored as JSON)**
            $teamMemberIds = json_decode($post->team, true); // Convert JSON string to array
            $teamMembers = [];

            if (is_array($teamMemberIds) && count($teamMemberIds) > 0) {
                $teamMembers = User::whereIn('id', $teamMemberIds)->select('id', 'first_name', 'last_name')->get();
            }

            // **Add Team Members to Response**
            $post['team_members'] = $teamMembers;

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Post details fetched successfully',
                'data'    => $post
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error'   => $e->getMessage(),
                'data'    => null
            ], 500);
        }
    }



    public function post_status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id'          => 'required|integer', // Ensure ID is an integer
            'status'      => 'required|string|in:Scheduled,Draft,Published', // Ensure status is valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the task record by ID only
            $posts = Post::find($request->input('id'));

            // Check if the task exists
            if (!$posts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found.'
                ], 404);
            }

            // Allowed statuses
            $statuses = ['Scheduled', 'Draft', 'Published'];

            // Check if the provided status is valid
            if (!in_array($request->status, $statuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ], 400);
            }

            // Update the task status in the database
            $posts->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => "Status changed to {$request->status}",
                'new_status' => $request->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function PostApprovalStatus(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id'                => 'required|integer', // Ensure ID is an integer
            'approval_status'   => 'required|string|in:Under Review,Rejected,Approved', // Ensure approval status is valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the post record by ID
            $post = Post::find($request->input('id'));

            // Check if the post exists
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found.'
                ], 404);
            }

            // Allowed approval statuses
            $allowedApprovalStatuses = ['Under Review', 'Rejected', 'Approved'];

            // Check if the provided approval_status is valid (this is redundant due to validation but added for clarity)
            if (!in_array($request->approval_status, $allowedApprovalStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid approval status provided.'
                ], 400);
            }

            // Update the post's approval status in the database
            $post->update(['approval_status' => $request->approval_status]);

            return response()->json([
                'success' => true,
                'message' => "Approval status changed to {$request->approval_status}",
                'new_approval_status' => $request->approval_status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the approval status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
