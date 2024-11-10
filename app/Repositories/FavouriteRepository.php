<?php

namespace App\Repositories;

use App\Models\Products\Favourite;
use App\Models\Products\Product;
use Illuminate\Support\Str;

class FavouriteRepository
{
    public function __construct(
        private readonly Favourite $favouriteModel,
    )
    {
    }

    public function findByUserProductIds($userId, $dataIds)
    {
        return $this->favouriteModel->where('user_id', $userId)->whereIn('data_id', $dataIds)->get();
    }

    public function createOrDelete($userId, $dataId, $data = null, $type = null)
    {
        $favourite = $this->favouriteModel->where('user_id', $userId)->where('data_id', $dataId)->first();
        if ($favourite) {
            return $favourite->forceDelete();
        }
        $favourite = new $this->favouriteModel;

        $storeData=null;
        if($data){
            $storeData=[
                'company_id' => $data['company_id'],
                'title' => $data['title'],
            ];
        }
        return $this->setFavouriteAttributes($favourite, $userId, $dataId, $storeData, $type);
    }

    private function setFavouriteAttributes($favourite, $userId, $dataId, $data, $type = null)
    {
        $favourite->user_id = $userId;
        $favourite->data_id = $dataId;
        $favourite->data = $data;
        $favourite->type = $type;
        $favourite->save();
        return $favourite;
    }
}
