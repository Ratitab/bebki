<?php

namespace App\Repositories;

use App\Models\Products\Limit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LimitRepository
{
    public function __construct(
        private readonly Limit $limitModel,
    )
    {
    }
    public function findById($createdById)
    {
        return $this->limitModel->where('created_by._id', $createdById)->where('expires_at', '>=', Carbon::now()->format('Y-m-d'))->first();
    }

    public function useLimit($createdById)
    {
        $limit = $this->findById($createdById);
        if (!$limit) {
            return false;
        }
        if ($limit->limit_count <= 0) {
            return false;
        }
        $limit->limit_count--;
        $limit->save();
        return true;
    }
    public function create($createdBy, $user, $price, $package,$bought_limits, $limit_count, $limit_for, $expires_at)
    {
        $limit = new $this->limitModel;
        return $this->setLimitAttributes(
            $limit,
            $createdBy,
            $user,
            $price,
            $package,
            $bought_limits,
            $limit_count,
            $limit_for,
            $expires_at
        );
    }

    public function update($id, $createdBy, $user, $price, $package,$bought_limits, $limit_count, $limit_for, $expires_at)
    {
        $limit = $this->limitModel->find($id);
        return $this->setLimitAttributes(
            $limit,
            $createdBy,
            $user,
            $price,
            $package,
            $bought_limits,
            $limit_count,
            $limit_for,
            $expires_at
        );
    }

    private function setLimitAttributes($limit, $createdBy, $user, $price, $package,$bought_limits, $limit_count, $limit_for, $expires_at)
    {
        $limit->created_by = ['id' => $createdBy['id'], 'type' => $createdBy['type']];
        $limit->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
        $limit->price = $price;
        $limit->package = $package;
        $limit->bought_limits = $package;
        $limit->limit_count = $bought_limits;
        $limit->limit_for = $limit_for;
        $limit->expires_at = $expires_at;
        $limit->save();
        return $limit;
    }

    public function delete($id)
    {
        return $this->limitModel->where('_id', $id)->delete();
    }
}
