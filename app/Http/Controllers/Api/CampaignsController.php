<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;
use App\Models\User;
use App\Models\Campaigns;

class CampaignsController extends Controller
{

    public function CreateCampaignsUpdate(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'campaign_name'     => 'required',
                'campaign_type'     => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // **Check if ID exists (Update case)**
            if (!empty($request->id)) {
                $campaign = Campaigns::find($request->id);

                if (!$campaign) {
                    return response()->json(['message' => 'Campaign not found.'], 404);
                }
            } else {
                $campaign = new Campaigns(); // **Create New Campaign Instance**
            }

            // **Set Fields (Keeps Old Values If Not Provided)**
            $campaign->campaign_name      = $request->campaign_name ?? $campaign->campaign_name;
            $campaign->campaign_type      = $request->campaign_type ?? $campaign->campaign_type;
            $campaign->platforms          = $request->platforms ?? $campaign->platforms;
            $campaign->campaign_goal      = $request->campaign_goal ?? $campaign->campaign_goal;
            $campaign->start_date         = $request->start_date ?? $campaign->start_date;
            $campaign->end_date           = $request->end_date ?? $campaign->end_date;
            $campaign->team               = $request->team ?? $campaign->team;
            $campaign->media_upload       = $request->media_upload ?? $campaign->media_upload;
            $campaign->campaign_status    = $request->campaign_status ?? 'draft'; // Default campaign_status = 'draft'
            $campaign->attempted_users    = $request->attempted_users ?? $campaign->attempted_users;
            $campaign->sent_users         = $request->sent_users ?? $campaign->sent_users;
            $campaign->read_users         = $request->read_users ?? $campaign->read_users;
            $campaign->replied_users      = $request->replied_users ?? $campaign->replied_users;
            $campaign->notes              = $request->notes ?? $campaign->notes;
            $campaign->ad_type            = $request->ad_type ?? $campaign->ad_type;
            $campaign->destination_url    = $request->destination_url ?? $campaign->destination_url;
            $campaign->goal               = $request->goal ?? $campaign->goal;
            $campaign->topic              = $request->topic ?? $campaign->topic;
            $campaign->engagement         = $request->engagement ?? $campaign->engagement;
            $campaign->target_audience    = $request->target_audience ?? $campaign->target_audience;
            $campaign->link_clicks        = $request->link_clicks ?? $campaign->link_clicks;
            $campaign->branches           = $request->branches ?? $campaign->branches;
            $campaign->ad_copy            = $request->ad_copy ?? $campaign->ad_copy;
            $campaign->video_url          = $request->video_url ?? $campaign->video_url;
            $campaign->budget             = $request->budget ?? $campaign->budget;
            $campaign->distribution_area  = $request->distribution_area ?? $campaign->distribution_area;
            $campaign->distribution_method = $request->distribution_method ?? $campaign->distribution_method;
            $campaign->total_quantity_distributed = $request->total_quantity_distributed ?? $campaign->total_quantity_distributed;

            $campaign->save();

            $message = !empty($request->id) ? 'Campaign updated successfully.' : 'Campaign created successfully.';

            // **Return JSON response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'campaign' => $campaign
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
            // **Base Query (Fetch Campaigns with Status)**
            $campaigns = Campaigns::whereIn('campaign_status', ['active', 'paused', 'completed', 'draft'])
                ->withCount([ // Counts related metrics for campaigns
                    'tasks as completed_tasks_count' => function ($query) {
                        $query->where('status', 'Completed'); // Counts tasks where status is "Completed"
                    },
                    'tasks as to_do_tasks_count' => function ($query) {
                        $query->where('status', 'To Do'); // Counts tasks where status is "To Do"
                    },
                    'tasks as in_progress_tasks_count' => function ($query) {
                        $query->where('status', 'In Progress'); // Counts tasks where status is "In Progress"
                    },
                    'tasks as under_review_tasks_count' => function ($query) {
                        $query->where('status', 'Under Review'); // Counts tasks where status is "Under Review"
                    },
                    // Total task count for each campaign
                    'tasks as total_tasks_count' => function ($query) {
                        $query->whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed']); // Counts all tasks with any of the 4 statuses
                    }
                ]);

            // **Search Filter**
            if (!empty($request->search)) {
                $search = $request->search;
                $campaigns->where(function ($query) use ($search) {
                    $query->where('campaign_name', 'LIKE', "%{$search}%")
                        ->orWhere('platforms', 'LIKE', "%{$search}%")
                        ->orWhere('campaign_goal', 'LIKE', "%{$search}%")
                        ->orWhere('notes', 'LIKE', "%{$search}%")
                        ->orWhere('team', 'LIKE', "%{$search}%");
                });
            }

            // **Filter by Campaign Status**
            if ($request->has('campaign_status')) {
                $campaigns->where('campaign_status', $request->campaign_status);
            }

            // **Filter by Campaign Name**
            if ($request->has('campaign_name')) {
                $campaigns->where('campaign_name', 'LIKE', "%{$request->campaign_name}%");
            }

            // **Filter by Start Date**
            if (!empty($request->start_date)) {
                $campaigns->where('start_date', '>=', $request->start_date);
            }

            // **Filter by End Date**
            if (!empty($request->end_date)) {
                $campaigns->where('end_date', '<=', $request->end_date);
            }

            // **Filter by Team Members' Employee ID (in team JSON field)**
            if ($request->has('employee_id')) {
                $employeeId = $request->input('employee_id');
                $campaigns->whereRaw("JSON_CONTAINS(team, ?)", [json_encode((string) $employeeId)]);
            }

            // **Total Count (before pagination)**
            $count = $campaigns->count();

            // **Pagination Parameters**
            $limit = $request->input('limit', 10); // Default: 10 records per page
            $page = $request->input('page', 1);

            // **Sorting**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $campaigns->orderBy($request->sort_by, $sortOrder);
            } else {
                $campaigns->orderBy('id', 'desc');
            }

            // **Apply Pagination**
            $campaigns = $campaigns->skip($limit * ($page - 1))->take($limit)->get();

            // **Process Team Members' Names**
            $campaigns->map(function ($campaign) {
                $team_ids = json_decode($campaign->team, true) ?? [];

                $team_members = User::whereIn('id', $team_ids)
                    ->select('first_name', 'last_name')
                    ->get()
                    ->map(function ($user) {
                        return trim($user->first_name . ' ' . $user->last_name);
                    });

                $campaign->team_names = $team_members;
                return $campaign;
            });

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Campaign list fetched successfully.',
                'total'   => $count,
                'data'    => $campaigns
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching campaigns.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }




    public function details(Request $request)
    {
        try {
            // **Step 1: Validate Request**
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer', // Ensure ID is an integer
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data'    => null
                ], 422);
            }

            // **Step 2: Fetch Campaign Details**
            $campaign = Campaigns::where('id', $request->id)  
                ->first(); // Fetch the first campaign, or null if not found.

            if (!$campaign) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign not found',
                    'data'    => null
                ], 404);
            }

            // **Process Team Members' Names**
            $team_ids = json_decode($campaign->team, true) ?? [];

            $team_members = User::whereIn('id', $team_ids)
                ->select('id', 'first_name', 'last_name')
                ->get()
                ->map(function ($user) {
                    return trim($user->first_name . ' ' . $user->last_name); // Get full name of the team members
                });

            // Add the team members' names to the campaign object
            $campaign->team_names = $team_members;

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Campaign details fetched successfully',
                'data'    => $campaign
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


    public function Campaigns_status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer', // Ensure ID is an integer
            'status' => 'required|string|in:To Do,In Progress,Under Review,Completed' // Ensure status is valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the project record by ID
            $campaigns = Campaigns::find($request->input('id'));

            if (!$campaigns) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid project ID'
                ], 404);
            }

            // Allowed statuses
            $statuses = ['To Do', 'In Progress', 'Under Review', 'Completed'];

            // Check if the provided status is valid
            if (!in_array($request->status, $statuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ], 400);
            }

            // Update status in database
            $campaigns->update(['status' => $request->status]);

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


}
