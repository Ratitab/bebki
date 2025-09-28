<?php

namespace App\Repositories;

use App\Models\Blog\Blog;

class BlogRepository
{
    public function __construct(
        private readonly Blog $blogModel,
    )
    {
    }

    public function findMany($search)
    {
        $query = $this->blogModel;

        if ($search) {
            $searchTerm = $search;
            $query = $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
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

    public function create($user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    {
        $blog = new $this->blogModel;
        return $this->setBlogAttributes(
            $blog, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls
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

    private function setBlogAttributes($blog, $user, $slug,$title,$smallDescription,$text, $category, $imageUrls)
    {


        $blog->representative = ['user_id' => $user->id, 'name' => $user->information['first_name'] . ' ' . $user->information['last_name']];
        $blog->slug = $slug;
        $blog->title = $title;
        $blog->small_description = $smallDescription;
        $blog->text = $text;
        $blog->category = $category;
        if (!isset($blog->views_count)) {
            $blog->views_count = 0;
        }
        $blog->image_urls = $imageUrls;

        $blog->save();
        return $blog;
    }

    public function delete($id)
    {
        return $this->blogModel->where('_id', $id)->delete();
    }
}
