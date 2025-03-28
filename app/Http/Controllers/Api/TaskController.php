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

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    // public function list(Request $request)
    // {
    //     try {
    //         // **Base Query (Fetch Tasks)**
    //         $tasks = Task::whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed']);

    //         // **Filter by Project ID (if provided)**
    //         if ($request->has('project_id')) {
    //             $tasks->where('project_id', $request->project_id);
    //         }

    //         // **Search Filter**
    //         if (!empty($request->search)) {
    //             $search = $request->search;
    //             $tasks->where(function ($query) use ($search) {
    //                 $query->where('task_title', 'LIKE', "%{$search}%")
    //                     ->orWhere('priority', 'LIKE', "%{$search}%")
    //                     ->orWhere('department', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // **Filter by Task Status (if provided)**
    //         if ($request->has('status') && in_array($request->status, ['To Do', 'In Progress', 'Under Review', 'Completed'])) {
    //             $tasks->where('status', $request->status);
    //         }


    //         if ($request->has('priority') && in_array($request->priority, ['Low', 'Medium', 'Migh'])) {
    //             $tasks->where('priority', $request->priority);
    //         }
    //         // **Filter by Task Visibility (if provided)**
    //         if (!empty($request->visibility)) {
    //             $tasks->where('visibility', $request->visibility);
    //         }

    //         // **Sorting**
    //         if (!empty($request->sort_by)) {
    //             $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
    //             $tasks->orderBy($request->sort_by, $sortOrder);
    //         } else {
    //             $tasks->orderBy('id', 'desc');
    //         }

    //         // **Pagination Parameters**
    //         $limit = $request->input('limit', 10); // Default: 10 records per page
    //         $page = $request->input('page', 1);

    //         // **Pagination using built-in paginate method**
    //         $tasks = $tasks->paginate($limit);

    //         // **Step 5: Fetch Team Members for Each Task (if needed based on project team)**
    //         $tasks->getCollection()->transform(function ($task) {
    //             // Check if the task has a valid associated project and fetch the team
    //             if ($task->project_id) {
    //                 $project = Project::find($task->project_id); // Assuming you have a Project model

    //                 if ($project) {
    //                     $teamLeaderIds = json_decode($project->team, true); // Assuming 'team' is a JSON field in the project
    //                     $teamMembers = [];

    //                     if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
    //                         $teamMembers = User::whereIn('id', $teamLeaderIds)
    //                             ->select('id', 'first_name', 'last_name')
    //                             ->get();
    //                     }

    //                     // Add team members to the task data
    //                     $task->team_leaders = $teamMembers;
    //                 }
    //             }

    //             return $task;
    //         });

    //         // **Return Response**
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Task list fetched successfully.',
    //             'total'   => $tasks->total(),  // Total count from paginate
    //             'data'    => $tasks->items()   // Paginated data (current page tasks)
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error occurred while fetching tasks.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }



    public function list(Request $request)
    {
        try {
            // **Base Query (Fetch Tasks)**
            $tasks = Task::whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed']);

            // **Filter by Project ID (if provided)**
            if ($request->has('project_id')) {
                $tasks->where('project_id', $request->project_id);
            }

            // **Search Filter**
            if (!empty($request->search)) {
                $search = $request->search;
                $tasks->where(function ($query) use ($search) {
                    $query->where('task_title', 'LIKE', "%{$search}%")
                        ->orWhere('priority', 'LIKE', "%{$search}%")
                        ->orWhere('department', 'LIKE', "%{$search}%");
                });
            }

            // **Filter by Task Status (if provided)**
            if ($request->has('status') && in_array($request->status, ['To Do', 'In Progress', 'Under Review', 'Completed'])) {
                $tasks->where('status', $request->status);
            }

            // **Filter by Priority**
            if ($request->has('priority') && in_array($request->priority, ['Low', 'Medium', 'High'])) {
                $tasks->where('priority', $request->priority);
            }

            // **Filter by Task Visibility (if provided)**
            if (!empty($request->visibility)) {
                $tasks->where('visibility', $request->visibility);
            }

            // **Sorting**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $tasks->orderBy($request->sort_by, $sortOrder);
            } else {
                $tasks->orderBy('id', 'desc');
            }

            // **Pagination Parameters**
            $limit = $request->input('limit', 10); // Default: 10 records per page
            $page = $request->input('page', 1);

            // **Pagination using built-in paginate method**
            $tasks = $tasks->paginate($limit);

            $tasks->getCollection()->transform(function ($task) {
                // Initialize team_leaders as an empty array
                $task->team_leaders = [];
            
                // Check if the task has a team field
                if (!empty($task->team)) {
                    $teamLeaderIds = json_decode($task->team, true); // Fetch team from task itself
            
                    if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
                        $teamMembers = User::whereIn('id', $teamLeaderIds)
                            ->select('id', 'first_name', 'last_name')
                            ->get()
                            ->toArray();
            
                        // Only assign team_leaders if members exist
                        if (!empty($teamMembers)) {
                            $task->team_leaders = $teamMembers;
                        }
                    }
                }
            
                return $task;
            });
            


            $tasks->getCollection()->transform(function ($task) {
                // Don't add team_leaders by default
                if ($task->project_id) {
                    $project = Project::find($task->project_id);

                    if ($project && !empty($project->team)) {
                        $teamLeaderIds = json_decode($project->team, true);
                         $teamMembers=array();
                        if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
                            $teamMembers = User::whereIn('id', $teamLeaderIds)
                                ->select('id', 'first_name', 'last_name')
                                ->get()
                                ->toArray();

                            // // Add team_leaders only if there are members
                            // if ($teamMembers->isNotEmpty()) {
                            //     $task->team_leaders = $teamMembers;
                            // }
                        }
                        if(count($teamMembers)>0)
                        {
                            $task->team_leaders = $teamMembers;
                        }
                        else {
                            $task->team_leaders = [];
                        }
                    }

                }

                return $task;
            });

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'Task list fetched successfully.',
                'total'   => $tasks->total(),  // Total count from paginate
                'data'    => $tasks->items()   // Paginated data (current page tasks)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching tasks.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }





    public function store(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'id'          => 'nullable', // Optional for update case
                'project_id'  => 'required',
                'task_title'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // **Check if Task Exists by `id` (for Update Case)**
            $task = $request->has('id') ? Task::find($request->id) : null;

            // **Department Logic**
            if ($request->filled('department')) {
                $department_name = $request->department ?? null; // Request में दिया गया department
            } else {
                $department_name = DB::table('users')->value('department'); // Default department
            }

            // **Prepare Data for Insert/Update**
            $data = [
                'task_title'   => $request->task_title ?? null,
                'task_type'    => $request->task_type ?? null,
                'due_date'     => $request->due_date ?? null,
                'priority'     => $request->priority ?? null,
                'department'   => $department_name, // ✅ now department will be updated
                'team'         => json_encode($request->team ?? []),
                'link'         => $request->link,
                'visibility'   => $request->visibility,
                'attachment'   => json_encode($request->attachment ?? []),
                'description'  => $request->description ?? null,
                'project_id'   => $request->has('project_id') && is_numeric($request->project_id)
                    ? $request->project_id
                    : null,
            ];

            if ($task) {
                // **Update Existing Task**
                $task->update($data);
                $message = 'Task updated successfully.';
            } else {
                // **Create New Task**
                $data['project_id'] = $request->project_id;
                $task = Task::create($data);
                $message = 'Task created successfully.';
            }

            // **Notification Logic**
            $notifyService = new NotificationService();

            // Ensure that 'team' is an array, even if it's passed as a JSON string or array
            $teams = $request->has('team')
                ? (is_array($request->input('team'))
                    ? $request->input('team')
                    : json_decode($request->input('team'), true))
                : [];  // Initialize as empty array if no 'team' input is found

            if (!is_array($teams)) {
                $teams = [];  // Ensure $teams is an array
            }

            // Construct the message once for the task
            // $body = $request->description ?? 'A new task has been created/updated.';

            $heading = "The task {$request->task_title} has been assigned to you.";
            $body = $request->description;  // Correct assignment

            // Send Notification only once after task creation or update (general notification to all team members)
            foreach ($teams as $teamMemberId) {
                $notifyService->createNotification(
                    $teamMemberId,  // user_id (team member ID)
                    $body,           // body message
                    $heading         // notification heading
                );
            }


            // **Return JSON response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'task'    => $task
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while processing the request.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function details(Request $request)
{
    try {
        // **Step 1: Validate Request**
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'data'    => null
            ], 422);
        }

        // **Step 2: Fetch Task Details**
        $task = Task::find($request->id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
                'data'    => null
            ], 404);
        }

        // **🔹 Refresh the Model to Get Latest Data**
        $task->refresh();

        // **Step 3: Fetch Department Name from Users Table**
        $department_name = DB::table('users')
            ->where('department', $task->department)
            ->value('department') ?? 'Unknown';

        // **Step 4: Fetch Team Members from the Task Itself**
        $teamLeaderIds = json_decode($task->team, true); // Fetch from Task instead of Project
        $team_members = [];

        if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
            $team_members = User::whereIn('id', $teamLeaderIds)
                ->select('id', 'first_name', 'last_name')
                ->get();
        }

        // **Step 5: Build Response**
        $response = [
            'id'          => $task->id,
            'project_id'  => $task->project_id,
            'task_title'  => $task->task_title,
            'task_type'   => $task->task_type,
            'due_date'    => $task->due_date,
            'priority'    => $task->priority,
            'department'  => $department_name,
            'team_leaders'=> $team_members, // ✅ Now coming from Task, not Project
            'link'        => $task->link,
            'visibility'  => $task->visibility,
            'attachments' => $task->attachments,
            'description' => $task->description,
            'status'      => $task->status,
            'task_status' => $task->task_status,
            'created_at'  => $task->created_at,
            'updated_at'  => $task->updated_at,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Task details fetched successfully',
            'data'    => $response
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



    public function status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer', // Ensure ID is an integer
            'status' => 'required|string|in:To Do,In Progress,Under Review,Completed', // Ensure status is valid
            'project_id' => 'required|exists:projects,id' // Ensure project exists in the database
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the task record by ID and project_id
            $task = Task::where('id', $request->input('id'))
                ->where('project_id', $request->input('project_id'))
                ->firstOrFail();  // This will throw an exception if the task is not found

            // Allowed statuses
            $statuses = ['To Do', 'In Progress', 'Under Review', 'Completed'];

            // Check if the provided status is valid
            if (!in_array($request->status, $statuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.'
                ], 400);
            }

            // Update the task status in the database
            $task->status = $request->status;
            $task->save(); // Save the updated task

            // Return the updated task
            return response()->json([
                'success' => true,
                'message' => "Status Updated Sucessfully {$request->status}",
                'task' => $task  // Return the updated task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function task_status(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'id' => 'required|exists:tasks,id',
            'project_id' => 'required|exists:projects,id',
            'task_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            // Find the task by ID and project_id
            $task = Task::where('id', $request->input('id'))
                ->where('project_id', $request->input('project_id'))
                ->firstOrFail();

            // Update task status
            $task->task_status = $request->input('task_status');

            // Save the updated status
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully.',
                'data' => $task
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found for the given project.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the task status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function Priority_status(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer', // Ensure ID is an integer
            'priority' => 'required|string|in:Low,Medium,High', // Ensure status is valid
            'project_id' => 'required|exists:projects,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Find the task record by ID
            $task = Task::findOrFail($request->input('id'))
                ->where('project_id', $request->input('project_id'))
                ->firstOrFail(); // Use findOrFail to throw an exception if task is not found

            // The allowed priority statuses
            $statuses = ['Low', 'Medium', 'High']; // Correct array format for valid priority statuses

            // Check if the provided priority status is valid (this is actually already validated in the request)
            if (!in_array($request->priority, $statuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid priority status provided.'
                ], 400);
            }

            // Update the task's priority status in the database
            $task->priority = $request->priority;
            $task->save(); // Save the updated task

            // Return the updated task
            return response()->json([
                'success' => true,
                'message' => "Priority Status Updated Successfully to {$request->priority}",
                'task' => $task  // Return the updated task
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function github_frontend_date()
    {

    }

    public function github_backend_date()
    {

    }
}
