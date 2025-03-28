<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Project;

class Comment extends Model
{
    use HasFactory;


    protected $guarded = [];




    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // ✅ Define relationship with Task
    public function task()
    {
        return $this->belongsTo(Task::class, 'id');
    }
    protected static function boot()
    {
        parent::boot();

        Comment::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                // $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
