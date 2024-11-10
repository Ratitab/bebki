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

    public function countUserFavourites($userId)
    {
        return $this->favouriteModel->where('user_id', $userId)->count();
    }

    public function userFavouriteProducts($userId)
    {
        return $this->favouriteModel
            ->raw(function($collection) use ($userId) {
                return $collection->aggregate([
                    [
                        '$match' => [
                            'user_id' => $userId
                        ]
                    ],
                    [
                        '$addFields' => [
                            'objectId_data_id' => [
                                '$toObjectId' => '$data_id'
                            ]
                        ]
                    ],
                    [
                        '$lookup' => [
                            'from' => 'products',
                            'localField' => 'objectId_data_id',
                            'foreignField' => '_id',
                            'as' => 'product'
                        ]
                    ],
                    [
                        '$unwind' => '$product'
                    ],
                    [
                        '$project' => [
                            'user_id' => 1,
                            'data_id' => 1,
                            'data' => 1,
                            'product' => [
                                'title' => 1,
                                'category' => 1,
                                'material' => 1,
                                'stamp' => 1,
                                'weight' => 1,
                                'gem' => 1,
                                'size' => 1,
                                'description' => 1,
                                'customization' => 1,
                                'city' => 1,
                                'views_count' => 1,
                                'price' => 1,
                                'image_urls' => 1
                            ]
                        ]
                    ]
                ]);
            });
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
                'company_id' => $data['created_by']['id'],
                'title' => $data['title'],
                'category' => $data['category'],
                'material' => $data['material'],
                'stamp' => $data['stamp'],
                'weight' => $data['weight'],
                'gem' => $data['gem'],
                'size' => $data['size'],
                'description' => $data['description'],
                'customization' => $data['customization'],
                'city' => $data['city'],
                'views_count' => $data['views_count'],
                'price' => $data['price'],
                'image_urls' => $data['image_urls']
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
