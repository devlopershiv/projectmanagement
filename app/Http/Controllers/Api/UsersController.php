<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    // public function list(Request $request)
    // {
    //     try {
    //         // **Base Query (Fetch Users with Status 0 or 1, Exclude Admins)**
    //         $userlist = User::whereIn('status', [0, 1])
    //             ->where('is_admin', '!=', 1);

    //         // **Filter Employees (Only Show Employees When is_employee = 1)**
    //         if ($request->filled('is_employee') && $request->is_employee == 1) {
    //             $userlist->where('is_employee', 1);
    //         }

    //         // **Filter Clients (Only Show Clients When is_client = 1)**
    //         if ($request->filled('is_client') && $request->is_client == 1) {
    //             $userlist->where('is_client', 1);
    //         }

    //         // **Search Filter** (Optimized for better readability and performance)
    //         if (!empty($request->search)) {
    //             $search = $request->search;
    //             $userlist->where(function ($query) use ($search) {
    //                 $query->where('first_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('last_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('email', 'LIKE', "%{$search}%")
    //                     ->orWhere('employee_id', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // **Filter by Status** (Simplified condition)
    //         if (isset($request->status) && in_array($request->status, [0, 1])) {
    //             $userlist->where('status', $request->status);
    //         }

    //         // **Filter by Designation** (Simplified check)
    //         if (!empty($request->designation)) {
    //             $userlist->where('designation', $request->designation);
    //         }

    //         // **Pagination** (using built-in paginate method)
    //         $limit = $request->input('limit', 10);
    //         $page = $request->input('page', 1);

    //         // **Sorting** (optimized to apply sorting only when needed)
    //         if (!empty($request->sort_by)) {
    //             $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
    //             $userlist->orderBy($request->sort_by, $sortOrder);
    //         } else {
    //             $userlist->orderBy('id', 'desc');
    //         }

    //         // **Apply Pagination for Users**
    //         $userlist = $userlist->paginate($limit);


    //         $userlist->getCollection()->transform(function ($user) use ($request) {
    //             // **Step 3: Fetch Projects Related to User (Based on team JSON containing user ID)**
    //             $projectsQuery = Project::whereJsonContains('team', $user->id);

    //             // If a specific project_id is passed in the request, filter further
    //             if ($request->filled('project_id')) {
    //                 $projectsQuery->where('id', $request->project_id);
    //             }

    //             // Fetch the projects
    //             $projects = $projectsQuery->get();

    //             // **Step 4: Iterate through the projects and fetch user details for each team member**
    //             $projects->transform(function ($project) use ($request) {
    //                 // Decode the 'team' column to get the list of team member IDs
    //                 $teamMemberIds = json_decode($project->team);

    //                 // Get the user details for each team member
    //                 $teamMembers = User::whereIn('id', $teamMemberIds)->get(['id', 'name', 'first_name', 'last_name']);

    //                 // Add the team members (with their first and last names) to the project
    //                 $project->team_members = $teamMembers;

    //                 // **Step 5: Fetch Assigned Tasks for the Project**
    //                 $tasksQuery = Task::where('project_id', $project->id)->select(['id', 'project_id', 'task_title', 'task_type']);

    //                 // If a specific task_id is provided, filter the tasks
    //                 if ($request->filled('task_id')) {
    //                     $tasksQuery->where('id', $request->task_id);
    //                 }

    //                 // Fetch the filtered tasks
    //                 $project->tasks = $tasksQuery->get();

    //                 return $project;
    //             });

    //             // Add projects to the user
    //             $user->projects = $projects;

    //             return $user;
    //         });


    //         // header('Access-Control-Allow-Origin : *');
    //         //    dd( $userlist);
    //         // **Return Response**
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User list fetched successfully.',
    //             'total'   => $userlist->total(),  // Using paginate's total count
    //             'data'    => $userlist->items()    // Using paginate's items
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error occurred while fetching users.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    ///  arvind sir
    // public function list(Request $request){
    //     try {
    //         // *Base Query (Fetch Users with Status 0 or 1, Exclude Admins)*
    //         $userlist = User::whereIn('status', [0, 1])
    //             ->where('is_admin', '!=', 1);

    //         // *Filter Employees*
    //         if ($request->filled('is_employee') && $request->is_employee == 1) {
    //             $userlist->where('is_employee', 1);
    //         }

    //         // *Filter Clients*
    //         if ($request->filled('is_client') && $request->is_client == 1) {
    //             $userlist->where('is_client', 1);
    //         }

    //         // *Search Filter*
    //         if (!empty($request->search)) {
    //             $search = $request->search;
    //             $userlist->where(function ($query) use ($search) {
    //                 $query->where('first_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('last_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('email', 'LIKE', "%{$search}%")
    //                     ->orWhere('employee_id', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // *Filter by Status*
    //         if ($request->filled('status') && in_array($request->status, [0, 1])) {
    //             $userlist->where('status', $request->status);
    //         }

    //         // *Filter by Designation*
    //         if ($request->filled('designation')) {
    //             $userlist->where('designation', $request->designation);
    //         }

    //         // *Sorting & Pagination*
    //         $limit = $request->input('limit', 10);
    //         $page = $request->input('page', 1);

    //         if ($request->filled('sort_by')) {
    //             $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
    //             $userlist->orderBy($request->sort_by, $sortOrder);
    //         } else {
    //             $userlist->orderBy('id', 'desc');
    //         }

    //         // *Apply Pagination for Users*
    //         $userlist = $userlist->paginate($limit);

    //         // *Transform User Collection*
    //         $userlist->getCollection()->transform(function ($user) use ($request) {
    //             // *Fetch Projects Related to User*
    //             $projectsQuery = Project::whereJsonContains('team', $user->id);

    //             // *Strict Filter by Project ID (if provided)*
    //             if ($request->filled('project_id')&& $request->project_id > 0) {
    //                 $projectsQuery->where('id', '=', $request->project_id);
    //             }

    //             // Fetch the projects
    //             $projects = $projectsQuery->get();

    //             // *Ensure Only Relevant Projects Appear*
    //             if ($projects->isEmpty()) {
    //                 $user->projects = null;
    //                 return null;  // Exclude this user if they have no matching projects
    //             }

    //             // *Iterate through projects to fetch user details & tasks*
    //             $projects->transform(function ($project) use ($request) {
    //                 $teamMemberIds = json_decode($project->team);

    //                 // *Get team member details*
    //                 $teamMembers = User::whereIn('id', $teamMemberIds)
    //                     ->get(['id', 'name', 'first_name', 'last_name']);
    //                 $project->team_members = $teamMembers;

    //                 // *Fetch tasks for the project*
    //                 $tasksQuery = Task::where('project_id', $project->id)
    //                     ->select(['id', 'project_id', 'task_title', 'task_type']);

    //                 // *Filter tasks by task_id (if provided)*
    //                 if ($request->filled('task_id') && $request->task_id > 0) {
    //                     $tasksQuery->where('id', '=', $request->task_id);
    //                 }

    //                 // Fetch tasks
    //                 $tasks = $tasksQuery->get();

    //                 // *Only add tasks if they exist*
    //                 $project->tasks = $tasks->isNotEmpty() ? $tasks : null;

    //                 return $project;
    //             });

    //             // *Only add projects if they exist*
    //             $user->projects = $projects->isNotEmpty() ? $projects : null;

    //             // *Filter Users with Matching Task* (Optional - if you want to include task filtering as well)
    //             if ($request->filled('task_id') && $user->projects) {
    //                 $userHasTask = $user->projects->contains(function ($project) use ($request) {
    //                     return $project->tasks && $project->tasks->contains('id', $request->task_id);
    //                 });

    //                 // If the user does not have the task_id, exclude them from the final list
    //                 if (!$userHasTask) {
    //                     return null;  // This will exclude the user from the final list
    //                 }
    //             }

    //             // Return user only if they are associated with the given project_id (or if no project filter is applied)
    //             if ($request->filled('project_id') && !$user->projects) {
    //                 return null;  // Exclude user if they do not have the specified project
    //             }

    //             return $user;
    //         });

    //         // *Filter out null users from the collection*
    //         $filteredUsers = $userlist->filter(function ($user) {
    //             return $user !== null;
    //         });

    //         // *Return Response*
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User list fetched successfully.',
    //             'total'   => $filteredUsers->count(),
    //             'data'    => $filteredUsers->values()->all()  // Reindex the array after filtering out null values
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error occurred while fetching users.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }




    // public function list(Request $request)
    // {
    //     try {
    //         // *Base Query (Fetch Users with Status 0 or 1, Exclude Admins)*
    //         $userlist = User::whereIn('status', [0, 1])
    //             ->where('is_admin', '!=', 1);

    //         // *Filter Employees*
    //         if ($request->filled('is_employee') && $request->is_employee == 1) {
    //             $userlist->where('is_employee', 1);
    //         }

    //         // *Filter Clients*
    //         if ($request->filled('is_client') && $request->is_client == 1) {
    //             $userlist->where('is_client', 1);
    //         }

    //         // *Search Filter*
    //         if (!empty($request->search)) {
    //             $search = $request->search;
    //             $userlist->where(function ($query) use ($search) {
    //                 $query->where('first_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('last_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('email', 'LIKE', "%{$search}%")
    //                     ->orWhere('employee_id', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // *Filter by Status*
    //         if ($request->filled('status') && in_array($request->status, [0, 1])) {
    //             $userlist->where('status', $request->status);
    //         }

    //         // *Filter by Designation*
    //         if ($request->filled('designation')) {
    //             $userlist->where('designation', $request->designation);
    //         }

    //         // *Sorting & Pagination*
    //         $limit = $request->input('limit', 10);
    //         $page = $request->input('page', 1);

    //         if ($request->filled('sort_by')) {
    //             $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
    //             $userlist->orderBy($request->sort_by, $sortOrder);
    //         } else {
    //             $userlist->orderBy('id', 'desc');
    //         }

    //         // *Apply Pagination for Users*
    //         $userlist = $userlist->paginate($limit);

    //         // *Transform User Collection*
    //         $filteredUsers = [];
    //         foreach ($userlist as $user) {
    //             // *Fetch Projects Related to User*
    //             $projectsQuery = Project::whereJsonContains('team', $user->id);

    //             // *Strict Filter by Project ID (if provided)*
    //             if ($request->filled('project_id') && $request->project_id > 0) {
    //                 $projectsQuery->where('id', '=', $request->project_id);
    //             }

    //             // Fetch the projects
    //             $projects = $projectsQuery->get();

    //             // If project filter is applied and no projects found, exclude user
    //             if ($request->filled('project_id') && $projects->isEmpty()) {
    //                 continue;
    //             }

    //             // *Iterate through projects to fetch user details & tasks*
    //             foreach ($projects as $project) {
    //                 $teamMemberIds = json_decode($project->team, true) ?? [];

    //                 // *Get team member details*
    //                 $teamMembers = User::whereIn('id', $teamMemberIds)
    //                     ->get(['id', 'name', 'first_name', 'last_name']);
    //                 $project->team_members = $teamMembers;

    //                 // *Fetch tasks for the project*
    //                 $tasksQuery = Task::where('project_id', $project->id)
    //                     ->select(['id', 'project_id', 'task_title', 'task_type']);

    //                 // *Filter tasks by task_id (if provided)*
    //                 if ($request->filled('task_id') && $request->task_id > 0) {
    //                     $tasksQuery->where('id', '=', $request->task_id);
    //                 }

    //                 // Fetch tasks
    //                 $tasks = $tasksQuery->get();

    //                 // *Only add tasks if they exist*
    //                 $project->tasks = $tasks->isNotEmpty() ? $tasks : null;
    //             }

    //             // *Only add projects if they exist*
    //             $user->projects = $projects->isNotEmpty() ? $projects : null;

    //             // *Filter Users with Matching Task* (if task filtering is applied)
    //             if ($request->filled('task_id')) {
    //                 $userHasTask = false;

    //                 foreach ($projects as $project) {
    //                     if ($project->tasks && $project->tasks->contains('id', $request->task_id)) {
    //                         $userHasTask = true;
    //                         break;
    //                     }
    //                 }

    //                 if (!$userHasTask) {
    //                     continue; // Skip this user
    //                 }
    //             }

    //             $filteredUsers[] = $user;
    //         }

    //         // *Return Response*
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User list fetched successfully.',
    //             'total'   => count($filteredUsers),
    //             'data'    => array_values($filteredUsers) // Reindex array after filtering
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error occurred while fetching users.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }



    // public function list(Request $request)
    // {
    //     try {
    //         // *Base Query (Fetch Users with Status 0 or 1, Exclude Admins)*
    //         $userlist = User::whereIn('status', [0, 1])
    //             ->where('is_admin', '!=', 1);

    //         // *Filter Employees*
    //         if ($request->filled('is_employee') && $request->is_employee == 1) {
    //             $userlist->where('is_employee', 1);
    //         }

    //         // *Filter Clients*
    //         if ($request->filled('is_client') && $request->is_client == 1) {
    //             $userlist->where('is_client', 1);
    //         }

    //         // *Search Filter*
    //         if (!empty($request->search)) {
    //             $search = $request->search;
    //             $userlist->where(function ($query) use ($search) {
    //                 $query->where('first_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('last_name', 'LIKE', "%{$search}%")
    //                     ->orWhere('email', 'LIKE', "%{$search}%")
    //                     ->orWhere('employee_id', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // *Filter by Status*
    //         if ($request->filled('status') && in_array($request->status, [0, 1])) {
    //             $userlist->where('status', $request->status);
    //         }

    //         // *Filter by Designation*
    //         if ($request->filled('designation')) {
    //             $userlist->where('designation', $request->designation);
    //         }

    //         // *Sorting*
    //         if ($request->filled('sort_by')) {
    //             $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
    //             $userlist->orderBy($request->sort_by, $sortOrder);
    //         } else {
    //             $userlist->orderBy('id', 'desc');
    //         }

    //         // *Check for Pagination*
    //         if ($request->filled('limit') || $request->filled('page')) {
    //             $limit = $request->input('limit', 10);
    //             $userlist = $userlist->paginate($limit);
    //         } else {
    //             $userlist = $userlist->get(); // Fetch all users if no pagination
    //         }

    //         // *Transform User Collection*
    //         $filteredUsers = [];
    //         foreach ($userlist as $user) {
    //             // *Fetch Projects Related to User*
    //             $projectsQuery = Project::whereJsonContains('team', $user->id);

    //             // *Strict Filter by Project ID (if provided)*
    //             if ($request->filled('project_id') && $request->project_id > 0) {
    //                 $projectsQuery->where('id', '=', $request->project_id);
    //             }

    //             // Fetch the projects
    //             $projects = $projectsQuery->get();

    //             // If project filter is applied and no projects found, exclude user
    //             if ($request->filled('project_id') && $projects->isEmpty()) {
    //                 continue;
    //             }

    //             // *Iterate through projects to fetch user details & tasks*
    //             foreach ($projects as $project) {
    //                 $teamMemberIds = json_decode($project->team, true) ?? [];

    //                 // *Get team member details*
    //                 $teamMembers = User::whereIn('id', $teamMemberIds)
    //                     ->get(['id', 'name', 'first_name', 'last_name']);
    //                 $project->team_members = $teamMembers;

    //                 // *Fetch tasks for the project*
    //                 $tasksQuery = Task::where('project_id', $project->id)
    //                     ->select(['id', 'project_id', 'task_title', 'task_type']);

    //                 // *Filter tasks by task_id (if provided)*
    //                 if ($request->filled('task_id') && $request->task_id > 0) {
    //                     $tasksQuery->where('id', '=', $request->task_id);
    //                 }

    //                 // Fetch tasks
    //                 $tasks = $tasksQuery->get();

    //                 // *Only add tasks if they exist*
    //                 $project->tasks = $tasks->isNotEmpty() ? $tasks : null;
    //             }

    //             // *Only add projects if they exist*
    //             $user->projects = $projects->isNotEmpty() ? $projects : null;

    //             // *Filter Users with Matching Task* (if task filtering is applied)
    //             if ($request->filled('task_id')) {
    //                 $userHasTask = false;

    //                 foreach ($projects as $project) {
    //                     if ($project->tasks && $project->tasks->contains('id', $request->task_id)) {
    //                         $userHasTask = true;
    //                         break;
    //                     }
    //                 }

    //                 if (!$userHasTask) {
    //                     continue; // Skip this user
    //                 }
    //             }

    //             $filteredUsers[] = $user;
    //         }

    //         // *Return Response*
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'User list fetched successfully.',
    //             'total'   => count($filteredUsers),
    //             'data'    => array_values($filteredUsers) // Reindex array after filtering
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error occurred while fetching users.',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function list(Request $request)
    {
        try {
            // *Base Query (Fetch Users with Status 0 or 1, Exclude Admins)*
            $userlist = User::whereIn('status', [0, 1])
                ->where('is_admin', '!=', 1);

            // *Filter Employees*
            if ($request->filled('is_employee') && $request->is_employee == 1) {
                $userlist->where('is_employee', 1);
            }

            // *Filter Clients*
            if ($request->filled('is_client') && $request->is_client == 1) {
                $userlist->where('is_client', 1);
            }

            // *Search Filter*
            if (!empty($request->search)) {
                $search = $request->search;
                $userlist->where(function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('employee_id', 'LIKE', "%{$search}%");
                });
            }

            // *Filter by Status*
            if ($request->filled('status') && in_array($request->status, [0, 1])) {
                $userlist->where('status', $request->status);
            }

            // *Filter by Designation*
            if ($request->filled('designation')) {
                $userlist->where('designation', $request->designation);
            }

            // *Sorting*
            if ($request->filled('sort_by')) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $userlist->orderBy($request->sort_by, $sortOrder);
            } else {
                $userlist->orderBy('id', 'desc');
            }

            // *Total Users Count Before Filtering by Projects and Tasks*
            $totalUsers = $userlist->count();

            // *Check for Pagination*
            if ($request->filled('limit') || $request->filled('page')) {
                $limit = $request->input('limit', 10);
                $userlist = $userlist->paginate($limit);
            } else {
                $userlist = $userlist->get();
            }

            // *Transform User Collection*
            $filteredUsers = [];
            foreach ($userlist as $user) {
                // *Fetch Projects Related to User*
                $projectsQuery = Project::whereJsonContains('team', $user->id);

                // *Strict Filter by Project ID (if provided)*
                if ($request->filled('project_id') && $request->project_id > 0) {
                    $projectsQuery->where('id', '=', $request->project_id);
                }

                // Fetch the projects
                $projects = $projectsQuery->get();

                // *Iterate through projects to fetch user details & tasks*
                foreach ($projects as $project) {
                    $teamMemberIds = json_decode($project->team, true) ?? [];

                    // *Get team member details*
                    $teamMembers = User::whereIn('id', $teamMemberIds)
                        ->get(['id', 'name', 'first_name', 'last_name']);
                    $project->team_members = $teamMembers;

                    // *Fetch tasks for the project*
                    $tasksQuery = Task::where('project_id', $project->id)
                        ->select(['id', 'project_id', 'task_title', 'task_type']);

                    // *Filter tasks by task_id (if provided)*
                    if ($request->filled('task_id') && $request->task_id > 0) {
                        $tasksQuery->where('id', '=', $request->task_id);
                    }

                    // Fetch tasks
                    $tasks = $tasksQuery->get();

                    // *Only add tasks if they exist*
                    $project->tasks = $tasks->isNotEmpty() ? $tasks : null;
                }

                // *Assign Projects to User*
                $user->projects = $projects;
                $filteredUsers[] = $user;
            }

            // *Return Response with Original Total Count*
            return response()->json([
                'success' => true,
                'message' => 'User list fetched successfully.',
                'total'   => $totalUsers, // Keep the total count unchanged
                'data'    => array_values($filteredUsers) // Reindex array after filtering
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching users.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function userCreateUpdate(Request $request)
    {
        try {
            // **Validation Rules**
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email|unique:users,email,' . $request->id,
                'password' => empty($request->id) ? 'required|min:8' : 'nullable|min:8',
            ]);

            if ($validator->fails()) {
                // Check for validation errors
                $errors = $validator->errors();

                // Check if email already exists
                if ($errors->has('email')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email already exists.',
                        'data'    => null
                    ], 422);
                }

                // Check for password validation errors
                if ($errors->has('password')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password must be at least 8 characters long, contain one uppercase letter, one lowercase letter, one number, and one special character.',
                        'data'    => null
                    ], 422);
                }

                return response()->json(['errors' => $errors], 422);
            }


            // **Check if ID exists (Update case)**
            if (!empty($request->id)) {
                $user = User::find($request->id);

                if (!$user) {
                    return response()->json(['message' => 'User not found.'], 404);
                }

                // **Update Only Provided Fields**
                $user->fill([
                    'name'                  => $request->name ?? $user->name,
                    'first_name'            => $request->first_name ?? $user->first_name,
                    'last_name'             => $request->last_name ?? $user->last_name,
                    'email'                 => $request->email ?? $user->email,
                    'phone_number'          => $request->phone_number ?? $user->phone_number,
                    'department'            => $request->department ?? $user->department,
                    'designation'           => $request->designation ?? $user->designation,
                    'joining_date'          => $request->joining_date ?? $user->joining_date,
                    'skill_set'             => $request->filled('skill_set') ? implode(',', (array) $request->skill_set) : $user->skill_set,
                    'is_admin'              => $request->is_admin ?? $user->is_admin,
                    'is_employee'           => $request->is_employee ?? $user->is_employee,
                    'is_client'             => $request->is_client ?? $user->is_client,
                    'contact_name'          => $request->contact_name ?? $user->contact_name,
                    'contact_person_number' => $request->contact_person_number ?? $user->contact_person_number,
                ]);

                // **Update Password If Provided**
                if (!empty($request->password)) {
                    $user->c_password = $request->password;
                    $user->password = Hash::make($request->password);
                }

                // **Assign Employee ID or Client ID (if changed to client)**
                if ($request->filled('is_client') && $request->is_client == 1 && empty($user->client_id)) {
                    $user->client_id = 'CLI-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
                }

                $user->save(); // ✅ **Saves changes without deleting existing data**
                $message = 'User updated successfully.';
            } else {
                // **Create New User**
                $user = User::create([
                    'name'                  => $request->name ?? null,
                    'first_name'            => $request->first_name,
                    'last_name'             => $request->last_name,
                    'email'                 => $request->email,
                    'phone_number'          => $request->phone_number,
                    'department'            => $request->department,
                    'designation'           => $request->designation,
                    'joining_date'          => $request->joining_date,
                    'skill_set'             => implode(',', (array) $request->skill_set),
                    'c_password'            => $request->password, // Store plain password for response
                    'password'              => Hash::make($request->password),
                    'is_admin'              => $request->is_admin ?? 0,
                    'is_employee'           => $request->is_employee ?? 0,
                    'is_client'             => $request->is_client ?? 0,
                    'contact_name'          => $request->contact_name ?? '',
                    'contact_person_number' => $request->contact_person_number ?? '',
                ]);

                // **Assign Employee ID or Client ID**
                if ($request->filled('is_employee') && $request->is_employee == 1) {
                    $user->employee_id = 'EMP-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
                } elseif ($request->filled('is_client') && $request->is_client == 1) {
                    $user->client_id = 'CLI-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
                }

                $user->save(); // ✅ Ensures IDs are assigned properly
                $message = 'User created successfully.';
            }

            // **Notification Logic**: Added notification after user creation or update
            $notifyService = new NotificationService();

            // Constructing body based on role
            if ($request->is_admin) {
                $role = 'Admin';
            } elseif ($request->is_employee) {
                $role = 'Employee';
            } elseif ($request->is_client) {
                $role = 'Client';
            } else {
                $role = 'User';
            }

            // Customizing notification message based on role
            $body = "You are assigned to a User as a $role: " . ($request->name ?? 'New User');

            foreach ($user as $value) {
                $notifyService->createNotification(
                    $value,               // user_id (assuming $value is user_id)
                    $body,                // body message
                    'User Role Assignment' // heading
                );
            }

            // **Return JSON Response**
            return response()->json([
                'success' => true,
                'message' => $message,
                'user'    => $user->makeHidden(['password'])->makeVisible(['c_password']), // Show plain password
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
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            // **Step 2: Fetch User Details**
            $user = User::findOrFail($request->id);

            // **Convert skill_set back to an array**
            if ($user->skill_set) {
                $user->skill_set = explode(',', $user->skill_set);
            } else {
                $user->skill_set = []; // Handle the case if skill_set is null
            }

            // **Step 3: Fetch Projects Related to User (Based on team JSON containing user ID)**
            $projects = Project::where(function ($query) use ($request, $user) {
                // Fetch projects where the user is in the team
                $query->whereJsonContains('team', $request->id)
                    // And where the client_id matches the user's id
                    ->orWhere('client_id', $user->id);
            })->get();

            // **Step 4: Iterate through the projects and fetch user details for each team member**
            $projects->transform(function ($project) {
                // Decode the 'team' column to get the list of team member IDs
                $teamMemberIds = json_decode($project->team);

                // Get the user details for each team member
                $teamMembers = User::whereIn('id', $teamMemberIds)->get(['id', 'name', 'first_name', 'last_name']);

                // Add the team members (with their first and last names) to the project
                $project->team_members = $teamMembers;

                return $project;
            });

            // **Step 5: Return the user details along with related projects**
            return response()->json([
                'success' => true,
                'message' => 'User details fetched successfully',
                'data' => [
                    'user' => $user,
                    'projects' => $projects
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }



    public function Users_status(Request $request)
    {
        // Validate the request
        $validator = validator($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            // Find the user record by ID or throw a ModelNotFoundException
            $user = User::where('id', $request->input('id'))->firstOrFail();

            // Toggle the status and set the message
            if ($user->status == 1) {
                $user->status = 0;
                $msg = "Active Successfully";
            } else {
                $user->status = 1;
                $msg = "Inactive Successfully ";
            }

            // Save the updated status
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $msg
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return error message if the record is not found
            return response()->json([
                'success' => false,
                'message' => 'Invalid ID to update'
            ], 404);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Function to get logged in user by token
    public function getuser(Request $request)
    {
        $user = Auth::user();
        if ($user != null) {
            // getting user details and access token from the database.
            $response = [
                'user' => $user,
                'success' => true,
                'message' => ''
            ];
        }
        return response()->json($response);
    }
}
