<?php

namespace App\Services;

use App\DTO\SearchProductsDTO;
use App\DTO\SingleProductDTO;
use App\Models\Products\Product;
use App\Repositories\BlogRepository;
use App\Repositories\BoostRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\FavouriteRepository;
use App\Repositories\FreeLimitRepository;
use App\Repositories\LimitRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogService
{
    public function __construct(private readonly BlogRepository $blogRepository)
    {
    }

    public function findMany($search = null)
    {
        return $this->blogRepository->findMany($search);
    }
    public function findOne($slug)
    {
        $product = $this->blogRepository->findOneByIdOrSlug($slug);
        if (!$product) {
            return null;
        }
        return $product;
    }
    public function create($user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    {
            return $this->blogRepository->create($user, $slug,$title,$smallDescription,$text, $category, $imageUrls);
    }

    public function update($id, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    {
        return $this->blogRepository->update($id, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls);
    }

    public function delete($blogId)
    {
        return $this->blogRepository->delete($blogId);
    }
}
