<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;


    protected $guarded = [];


    protected static function boot()
    {
        parent::boot();

        Post::creating(function($model) {
            $user = Auth::user();
            if($user!=null){
                $model->enteredbyid = $user->id;
                // $model->organisation_id = $user->active_organisation;
            }
        });
    }
}
