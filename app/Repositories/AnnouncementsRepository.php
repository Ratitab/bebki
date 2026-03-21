<?php

namespace App\Repositories;

use App\Models\Blog\Blog;
use App\Models\Announcements\Announcements;

class AnnouncementsRepository
{
    public function __construct(
        private readonly Announcements $announcementsModel,
    )
    {
    }

    public function findMany($search)
    {
        $query = $this->announcementsModel;

        if ($search) {
            $searchTerm = $search;
            $query = $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('small_description', 'like', "%{$searchTerm}%")
                    ->orWhere('slug', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        $query = $query->orderBy('specific_datetime', 'desc');

        return $query->paginate(12);
    }

    public function findOneById($id)
    {
        return $this->blogModel->where('_id', $id)->first();
    }

    public function findOneBySlug($slug)
    {
        return $this->blogModel->where('slug', $slug)->first();
    }


    public function findOneByIdOrSlug($identifier)
    {
        // Determine if it's a MongoDB ID or slug
        if ($this->isValidMongoId($identifier)) {
            return $this->findOneById($identifier);
        } else {
            return $this->findOneBySlug($identifier);
        }
    }

    private function isValidMongoId($id)
    {
        // MongoDB ObjectId validation
        return is_string($id) && preg_match('/^[0-9a-fA-F]{24}$/', $id);
    }

    public function create($user, $slug, $imageUrls)
    {
        $announcement = new $this->announcementsModel;
        return $this->setAnnouncementAttributes(
            $announcement, $user, $slug, $imageUrls
        );
    }

    public function update($id, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    {
        $blog = $this->findOneById($id);
        if (!$blog) {
            return null;
        }
        return $this->setBlogAttributes(
            $blog, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls
        );
    }

    private function setAnnouncementAttributes($announcement, $user, $slug, $imageUrls)
    {


        $announcement->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
        $announcement->slug = $slug;
        if (!isset($announcement->views_count)) {
            $announcement->views_count = 0;
        }
        $announcement->image_urls = $imageUrls;

        $announcement->save();
        return $announcement;
    }

    public function delete($id)
    {
        return $this->announcementsModel->where('_id', $id)->delete();
    }
}
