<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use function Laravel\Prompts\select;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function store(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'project_name'   => 'required',
                'client_id'      => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // **Check if ID exists (Update case)**
            if (!empty($request->id)) {
                // **Update Project Details**
                $project = Project::find($request->id);

                if (!$project) {
                    return response()->json(['message' => 'Project not found.'], 404);
                }

                // **Update the project with the provided data**
                $project->update([
                    'project_name'        => $request->project_name,
                    'client_id'           => $request->client_id,
                    'start_date'          => $request->start_date ?? null,
                    'due_date'            => $request->due_date,
                    'priority'            => $request->priority ?? null,
                    'project_leader'      => $request->project_leader ?? null, // Store leader ID
                    'project_stage'       => $request->project_stage ?? null,
                    'team'                => json_encode($request->team), // Store team names as JSON
                    'attachments'         => json_encode($request->attachments),
                    'description'         => $request->description,
                    'project_status'      => $request->has('project_status') ? $request->project_status : $project->project_status, // Preserve status if updating
                ]);

                $message = 'Project updated successfully.';
            } else {
                // **Create New Project**
                $project = Project::create([
                    'project_name'        => $request->project_name,
                    'client_id'           => $request->client_id ?? null,
                    'start_date'          => $request->start_date ?? null,
                    'due_date'            => $request->due_date,
                    'priority'            => $request->priority ?? null,
                    'project_leader'      => $request->project_leader ?? null, // Store leader ID
                    'project_stage'       => $request->project_stage ?? null,
                    'team'                => json_encode($request->team), // Store team names as JSON
                    'attachments'         => json_encode($request->attachments),
                    'description'         => $request->description,
                    'project_status'      => 1, // **Default project_status set to 1**
                ]);

                $message = 'Project created successfully.';

                // Send notification to team members after creating the project

                $notifyService = new NotificationService();
                $teams = $request->input('team'); // 'team' is already an array

                if (!is_array($teams)) {
                    return response()->json(['success' => false, 'message' => 'Invalid team data'], 400);
                }

                $body = 'You are assigned to a new project: ' . $project->project_name;

                foreach ($teams as $teamMemberId) {
                    // **Ensure user exists before creating a notification**
                    $user = User::find($teamMemberId);
                    if ($user) {
                        $notifyService->createNotification(
                            $teamMemberId,       // user_id (assuming $value is user_id)
                            $body,                // body message
                            'Project Assignment'  // heading
                        );
                    }
                }
            }

            // **Return JSON response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'project' => $project
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
            // **Base Query (Fetch Projects with Status)**
            $projects = Project::whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed'])
                ->with('client')
                ->withCount([ // Counts the number of related tasks per project
                    'tasks as completed_tasks_count' => function ($query) {
                        $query->where('status', 'Completed');  // Counts tasks where status is "Completed"
                    },
                    'tasks as to_do_tasks_count' => function ($query) {
                        $query->where('status', 'To Do');  // Counts tasks where status is "To Do"
                    },
                    'tasks as in_progress_tasks_count' => function ($query) {
                        $query->where('status', 'In Progress');  // Counts tasks where status is "In Progress"
                    },
                    'tasks as under_review_tasks_count' => function ($query) {
                        $query->where('status', 'Under Review');  // Counts tasks where status is "Under Review"
                    },
                    // Total task count for each project
                    'tasks as total_tasks_count' => function ($query) {
                        $query->whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed']);  // Counts all tasks with any of the 4 statuses
                    }
                ]);

            // **Search Filter**
            if (!empty($request->search)) {
                $search = $request->search;
                $projects->where(function ($query) use ($search) {
                    $query->where('project_name', 'LIKE', "%{$search}%")
                        ->orWhere('priority', 'LIKE', "%{$search}%")
                        ->orWhereHas('client', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                });
            }

            // **Filter by 'status'**

            if ($request->has('priority') && in_array($request->priority, ['Low', 'Medium', 'High'])) {
                $projects->where('priority', $request->priority);
            }
            

            if ($request->has('status') && in_array($request->status, ['To Do', 'In Progress', 'Under Review', 'Completed'])) {
                $projects->where('status', $request->status);
            }
            // **Filter by 'client_id'**
            if ($request->has('client_id')) {
                $clientId = $request->client_id;

                // Ensure we don't include null client_id values in the filter
                if (is_numeric($clientId) && !is_null($clientId)) {
                    $projects->where('client_id', $clientId);
                }
            }


            // **Filter by `employee_id` inside JSON field**
            $employeeId = $request->input('employee_id');
            if (!empty($employeeId)) {
                $projects->whereRaw("JSON_CONTAINS(team, ?)", [json_encode((string) $employeeId)]);
            }

            // **Filter by 'team_leader_id'**
            if ($request->has('team_id')) {
                $teamId = $request->input('team_id');  
               $projects=$projects->whereJsonContains('team', $teamId);
            }


            // **Total Count (before pagination)**
            $count = $projects->count();

            // **Pagination Parameters**
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            // **Sorting**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $projects->orderBy($request->sort_by, $sortOrder);
            } else {
                $projects->orderBy('id', 'desc');
            }

            // **Apply Pagination**
            $projects = $projects->skip($limit * ($page - 1))->take($limit)->get();

            // **Process Team Leaders & Project Leader**
            foreach ($projects as $project) {
                // **Decode team JSON field**
                $teamLeaderIds = json_decode($project->team, true);
                $teamLeaders = [];

                if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
                    $teamLeaders = User::whereIn('id', $teamLeaderIds)
                        ->select('id', 'employee_id', 'first_name', 'last_name')
                        ->get();

                    // **Filter team leaders by `employee_id` (if provided)**
                    if (!empty($employeeId)) {
                        $teamLeaders = $teamLeaders->filter(function ($teamLeader) use ($employeeId) {
                            return $teamLeader->employee_id == $employeeId;
                        })->values();
                    }
                }

                // **Assign filtered team leaders**
                $project->team_leaders = $teamLeaders;

                // **Get Project Leader Name**
                $leader = User::find($project->project_leader);
                $project->project_leader_name = $leader ? trim($leader->first_name . ' ' . $leader->last_name) : '';
            }

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Project list fetched successfully.',
                'total'   => $count,
                'data'    => $projects
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching projects.',
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
    
            // **Step 2: Fetch Project Details**
            $project = Project::with('project_leader')
                ->where('id', $request->id)
                ->with('client')
                ->withCount([ // Counts the number of related tasks per project
                    'tasks as completed_tasks_count' => function ($query) {
                        $query->where('status', 'Completed');  // Counts tasks where status is "Completed"
                    },
                    'tasks as to_do_tasks_count' => function ($query) {
                        $query->where('status', 'To Do');  // Counts tasks where status is "To Do"
                    },
                    'tasks as in_progress_tasks_count' => function ($query) {
                        $query->where('status', 'In Progress');  // Counts tasks where status is "In Progress"
                    },
                    'tasks as under_review_tasks_count' => function ($query) {
                        $query->where('status', 'Under Review');  // Counts tasks where status is "Under Review"
                    },
                    // Total task count for each project
                    'tasks as total_tasks_count' => function ($query) {
                        $query->whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed']);  // Counts all tasks with any of the 4 statuses
                    }
                ])
                ->first(); // Fetch the first project, or null if not found.
    
            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project not found',
                    'data'    => null
                ], 404);
            }
    
            // **Fetch Team Leaders if the project exists**
            $teamLeaderIds = json_decode($project->team, true); // Convert JSON string to array
    
            $teamLeaders = [];
            if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
                $teamLeaders = User::whereIn('id', $teamLeaderIds)
                    ->select('id', 'first_name', 'last_name')
                    ->get(); // Assuming team leaders are stored in the users table
            }
            
            // Assign the fetched team leaders to the project
            $project->team_leaders = $teamLeaders;
    
            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Project details fetched successfully',
                'data'    => $project
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
    


    public function project_status(Request $request)
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
            $project = Project::find($request->input('id'));

            if (!$project) {
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
            $project->update(['status' => $request->status]);

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



    public function status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id'             => 'required|integer', // Ensure ID is an integer
            'project_status' => 'required|integer|in:0,1' // Ensure project_status is either 0 or 1
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the project record by ID
            $project = Project::find($request->input('id'));

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid project ID'
                ], 404);
            }

            // Update project_status (Not status)
            $project->project_status = $request->project_status;
            $project->save();

            // Response message
            $msg = ($project->project_status == 1) ? "Project marked as Active" : "Project marked as Inactive";

            return response()->json([
                'success'     => true,
                'message'     => $msg,
                'new_status'  => $project->project_status // Return updated status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function Priority_status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer', // Ensure ID is an integer
            'priority' => 'required|string|in:Low,Medium,High', // Ensure status is valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the task record by ID
            $project = Project::findOrFail($request->input('id'));  // Use findOrFail to throw an exception if task is not found

            // The allowed priority statuses
            $statuses = ['Low', 'Medium', 'High']; // Correct array format for valid priority statuses

            // Check if the provided priority status is valid (this is actually already validated in the request)
            if (!in_array($request->priority, $statuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid priority status provided.'
                ], 400);
            }

            // Update the project's priority status in the database
            $project->priority = $request->priority;
            $project->save(); // Save the updated project

            // Return the updated project
            return response()->json([
                'success' => true,
                'message' => "Priority Status Updated Successfully to {$request->priority}",
                'task' => $project  // Return the updated project
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
