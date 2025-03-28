<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\User;
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
    public function list(Request $request)
    {
        try {
            // **Base Query (Fetch Users with Status 0 or 1, Exclude Admins)**
            $userlist = User::whereIn('status', [0, 1])
                ->where('is_admin', '!=', 1);

            // **Filter Employees (Only Show Employees When is_employee = 1)**
            if ($request->filled('is_employee') && $request->is_employee == 1) {
                $userlist->where('is_employee', 1);
            }

            if ($request->filled('is_client') && $request->is_client == 1) {
                $userlist->where('is_client', 1);
            }

            // **Search Filter**
            if (!empty($request->search)) {
                $search = $request->search;
                $userlist->where(function ($query) use ($search) {
                    $query->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('employee_id', 'LIKE', "%{$search}%");
                });
            }

            // **Filter by Status**
            if (isset($request->status) && in_array($request->status, [0, 1])) {
                $userlist->where('status', $request->status);
            }

            // **Filter by Designation**
            if (!empty($request->designation)) {
                $userlist->where('designation', $request->designation);
            }

            // **Total Count Before Pagination**
            $count = $userlist->count();

            // **Pagination**
            $limit = $request->input('limit', 10);
            $page = $request->input('page', 1);

            // **Sorting**
            if (!empty($request->sort_by)) {
                $sortOrder = ($request->sort_order == 1) ? 'asc' : 'desc';
                $userlist->orderBy($request->sort_by, $sortOrder);
            } else {
                $userlist->orderBy('id', 'desc');
            }

            // **Apply Pagination**
            $userlist = $userlist->skip($limit * ($page - 1))
                ->take($limit)
                ->get();

            // **Return Response**
            return response()->json([
                'success' => true,
                'message' => 'User list fetched successfully.',
                'total'   => $count,
                'data'    => $userlist
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching User.',
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
                'password' => empty($request->id) ? 'required|min:4' : 'nullable|min:4',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
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
            $teams = json_decode($request->input('team'), true);

            if (!is_array($teams)) {
                return response()->json(['success' => false, 'message' => 'Invalid team data'], 400);
            }

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
            $body = "You are assigned to a project as a $role: " . ($request->name ?? 'New User');

            foreach ($teams as $value) {
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
    
            // **Step 3: Fetch Projects Related to User (Assuming there is a user_id in projects)**
            // Assuming 'user_id' is the foreign key in the projects table that relates to the user
            $projects = Project::where('user_id', $user->id)->get();
    
            // **Step 4: Convert skill_set back to an array if it exists**
            if ($user->skill_set) {
                $user->skill_set = explode(',', $user->skill_set);
            } else {
                $user->skill_set = []; // Handle the case if skill_set is null
            }
    
            // **Step 5: Return the user details along with related projects**
            return response()->json([
                'success' => true,
                'message' => 'User details and related projects fetched successfully',
                'data' => [
                    'user' => $user,
                    'projects' => $projects, // Add projects data
                ]
            ], 200);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle user not found exception
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        } catch (\Exception $e) {
            // Handle any other exception
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
