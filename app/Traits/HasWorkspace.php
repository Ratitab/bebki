<?php

namespace App\Traits;


use App\Models\Workspaces\Workspace;
use App\Models\Workspaces\WorkspaceUser;
use App\Models\Workspaces\WorkspaceInformation;
use App\Models\Workspaces\WorkspaceInformationType;
use App\Models\Workspaces\WorkspaceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasWorkspace
{
    public function workspace_type()
    {
        return $this->belongsTo(WorkspaceType::class);
    }

    public function workspace_information()
    {
        return $this->hasMany(WorkspaceInformation::class);
    }

    public function workspace(){
        return $this->belongsTo(Workspace::class);
    }

    public function workspace_information_type(){
        return $this->belongsTo(WorkspaceInformationType::class);
    }

    public function workspace_information_fields($workspace_information_type_id){
        return WorkspaceInformationType::where('id',$workspace_information_type_id)->first();
    }

    public function workspace_information_values($id,$type_id)
    {
        return WorkspaceInformation::where('workspace_id',$id)->where('workspace_information_type_id',$type_id)->first();
    }

    public function workspaces(){
        return $this->hasMany(Workspace::class);
    }

    public function workspace_users(){
        return $this->hasMany(WorkspaceUser::class)->with(['workspace']);
    }


}

