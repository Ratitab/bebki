<?php

namespace App\Traits;

use \App\Models\Uploads\Upload;
use \App\Models\Uploads\SourceType;
use \App\Models\Uploads\UploadNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasUpload
{
    public function uploads(){
        return $this->hasMany(Upload::class);
    }

    public function upload(){
        return $this->belongsTo(\App\Models\Uploads\Upload::class);
    }

    public function source_type(){
        return $this->belongsTo(SourceType::class);
    }

    public function upload_nodes(){
        return $this->hasMany(UploadNode::class);
    }

    public function upload_node_type(){
        return $this->belongsTo(\App\Models\Uploads\UploadNodeType::class);
    }

    public function uploadable()
    {
        return $this->morphTo();
    }

    public function files(){
        return $this->morphMany(\App\Models\Uploads\UploadNode::class,'uploadable');
    }
}
