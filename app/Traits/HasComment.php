<?php

namespace App\Traits;

use App\Models\Feeds\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasComment
{
    public function comments(){
        return $this->hasMany(Comment::class)->orderBy('created_at','desc');
    }
}
