<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;

class Campaigns extends Model
{
    use HasFactory;

    protected $guarded = [];



    public function tasks()
    {
        return $this->hasMany(Task::class, 'project_id'); // Assuming 'Task' model has 'project_id' as foreign key
    }
    protected static function boot()
    {
        parent::boot();

        Campaigns::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                // $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
