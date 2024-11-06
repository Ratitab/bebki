<?php

namespace App\Repositories;

use App\Models\Products\FreeLimit;
use Illuminate\Support\Str;

class FreeLimitRepository
{
    public function __construct(
        private readonly FreeLimit $freeLimitModel,
    )
    {
    }
    public function findById($createdById)
    {
        return $this->freeLimitModel->where('created_by._id', $createdById)->first();
    }

    public function useLimit($createdBy, $user, $freeLimit_count = 2, $freeLimit_for = 'user')
    {
        $freeLimit = $this->freeLimitModel->where('created_by._id', $createdBy['id'])->first();
        if (!$freeLimit) {
            $this->create($createdBy, $user, $freeLimit_count, $freeLimit_for);
            return true;
        }
        if ($freeLimit->freeLimit_count <= 0) {
            return false;
        }
        $freeLimit->freeLimit_count--;
        $freeLimit->save();
        return true;
    }
    public function create($createdBy, $user, $freeLimit_count, $freeLimit_for)
    {
        $freeLimit = new $this->freeLimitModel;
        return $this->setLimitAttributes(
            $freeLimit,
            $createdBy,
            $user,
            $freeLimit_count,
            $freeLimit_for,
        );
    }

    public function update($id, $createdBy, $user, $freeLimit_count, $freeLimit_for)
    {
        $freeLimit = $this->freeLimitModel->find($id);
        return $this->setLimitAttributes(
            $freeLimit,
            $createdBy,
            $user,
            $freeLimit_count,
            $freeLimit_for
        );
    }

    private function setLimitAttributes($freeLimit, $createdBy, $user, $freeLimit_count, $freeLimit_for)
    {
        $freeLimit->created_by = ['id' => $createdBy['id'], 'type' => $createdBy['type']];
        $freeLimit->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
        $freeLimit->freeLimit_count = $freeLimit_count;
        $freeLimit->freeLimit_for = $freeLimit_for;
        $freeLimit->save();
        return $freeLimit;
    }

    public function delete($id)
    {
        return $this->freeLimitModel->where('_id', $id)->delete();
    }
}
