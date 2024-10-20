<?php

namespace App\Traits;



use App\Models\Departments\Department;
use App\Models\Departments\Position;
use App\Models\Jobs\JobStatus;
use App\Models\Jobs\JobType;
use App\Models\Terms\TermHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasJob{

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function position(){
        return $this->belongsTo(Position::class);
    }

    public function job_type(){
        return $this->belongsTo(JobType::class);
    }

    public function job_status(){
        return $this->belongsTo(JobStatus::class);
    }


}
