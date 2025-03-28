<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class Project extends Model
{
    use HasFactory;
    
    protected $guarded = [];


    public function project_leader(){
        return $this->belongsTo('App\Models\User','project_leader');
    }


    public function client(){
        return $this->belongsTo('App\Models\User','client_id');
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    // }

    public function tasks()
{
    return $this->hasMany(Task::class, 'project_id'); // Assuming 'Task' model has 'project_id' as foreign key
}
   
    protected static function boot()
    {
        parent::boot();

        Project::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                // $model->organisation_id = $user->active_organisation;
            }
        });
    }

  


}
