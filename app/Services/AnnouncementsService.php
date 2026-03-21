<?php

namespace App\Services;

use App\DTO\SearchProductsDTO;
use App\DTO\SingleProductDTO;
use App\Models\Products\Product;
use App\Repositories\AnnouncementsRepository;
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

class AnnouncementsService
{
    public function __construct(private readonly AnnouncementsRepository $announcementsRepository)
    {
    }

    public function findMany($search = null)
    {
        return $this->announcementsRepository->findMany($search);
    }
    public function findOne($slug)
    {
        $announcement = $this->announcementsRepository->findOneByIdOrSlug($slug);
        if (!$announcement) {
            return null;
        }
        return $announcement;
    }
    public function create($user, $slug, $imageUrls)
    {
            return $this->announcementsRepository->create($user, $slug, $imageUrls);
    }

    // public function update($id, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    // {
    //     return $this->blogRepository->update($id, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls);
    // }

    public function delete($announcementId)
    {
        return $this->announcementsRepository->delete($announcementId);
    }
}
