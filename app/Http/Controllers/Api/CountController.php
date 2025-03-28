<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;

class CountController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            // **User Counts**
            $totalUsers = User::whereIn('status', [0, 1])->where('is_admin', '!=', 1)->count();
            $totalEmployees = User::where('is_employee', 1)->whereIn('status', [0, 1])->count();
            $totalClients = User::where('is_client', 1)->whereIn('status', [0, 1])->count();

            // **Project Counts**
            $totalProjects = Project::whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed'])->count();
            $completedProjects = Project::where('status', 'Completed')->count();
            $ongoingProjects = Project::whereIn('status', ['To Do', 'In Progress', 'Under Review'])->count();

            // **Task Counts**
            $totalTasks = Task::whereIn('status', ['To Do', 'In Progress', 'Under Review', 'Completed'])->count();
            $pendingTasks = Task::whereIn('status', ['To Do', 'In Progress', 'Under Review'])->count();
            $completedTasks = Task::where('status', 'Completed')->count();

            // **Recent Projects (Last 5)**
            // $recentProjects = Project::withCount([
            //     'tasks',
            //     'tasks as completed_tasks_count' => function ($query) {
            //         $query->where('status', 'Completed');
            //     },
            //     'tasks as to_do_tasks_count' => function ($query) {
            //         $query->where('status', 'To Do');
            //     },
            //     'tasks as in_progress_tasks_count' => function ($query) {
            //         $query->where('status', 'In Progress');
            //     },
            //     'tasks as under_review_tasks_count' => function ($query) {
            //         $query->where('status', 'Under Review');
            //     }
            // ])
            // ->orderBy('created_at', 'desc')
            // ->get(['id', 'project_name', 'status', 'team']);


            // // Process each project to fetch and attach team leaders
            // $recentProjects->map(function ($project) {
            //     $teamLeaderIds = json_decode($project->team, true); // Decode JSON team IDs
            //     $team_members = [];

            //     if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
            //         $team_members = User::whereIn('id', $teamLeaderIds)
            //             ->select('id', 'first_name', 'last_name')
            //             ->get();
            //     }

            //     // Attach team leaders to the project object
            //     $project->team_leaders = $team_members;
            //     return $project;
            // });

            $recentProjects = Project::withCount([
                'tasks',
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('status', 'Completed');
                },
                'tasks as to_do_tasks_count' => function ($query) {
                    $query->where('status', 'To Do');
                },
                'tasks as in_progress_tasks_count' => function ($query) {
                    $query->where('status', 'In Progress');
                },
                'tasks as under_review_tasks_count' => function ($query) {
                    $query->where('status', 'Under Review');
                }
            ]);

            // **Apply team_id filter if provided in the request**
            if ($request->has('team_id')) {
                $teamId = $request->input('team_id');
                $recentProjects = $recentProjects->whereJsonContains('team', $teamId);
            }


            if ($request->has('client_id')) {
                $clientId = $request->client_id;

                // Ensure we don't include null client_id values in the filter
                if (is_numeric($clientId) && !is_null($clientId)) {
                    $recentProjects->where('client_id', $clientId);
                }
            }
            // **Fetch the Projects and Sort by Created Date**
            $recentProjects = $recentProjects->orderBy('created_at', 'desc')->get(['id', 'project_name', 'status', 'team']);

            // Process each project to fetch and attach team leaders
            $recentProjects->map(function ($project) {
                $teamLeaderIds = json_decode($project->team, true);  // Decode JSON team IDs
                $team_members = [];

                if (is_array($teamLeaderIds) && count($teamLeaderIds) > 0) {
                    $team_members = User::whereIn('id', $teamLeaderIds)
                        ->select('id', 'first_name', 'last_name')
                        ->get();
                }

                // Attach team leaders to the project object
                $project->team_leaders = $team_members;
                return $project;
            });

            // **Recent Tasks (Last 5)**
            $recentTasks = Task::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'task_title', 'status']);

            // **Return Dashboard Data**
            return response()->json([
                'success' => true,
                'message' => 'Dashboard data fetched successfully.',
                'data' => [
                    'users' => [
                        'total_users' => $totalUsers,
                        'total_employees' => $totalEmployees,
                        'total_clients' => $totalClients,
                    ],
                    'projects' => [
                        'total_projects' => $totalProjects,
                        'completed_projects' => $completedProjects,
                        'ongoing_projects' => $ongoingProjects,
                        'recent_projects' => $recentProjects,
                    ],
                    'tasks' => [
                        'total_tasks' => $totalTasks,
                        'pending_tasks' => $pendingTasks,
                        'completed_tasks' => $completedTasks,
                        'recent_tasks' => $recentTasks,
                    ],
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while fetching dashboard data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
