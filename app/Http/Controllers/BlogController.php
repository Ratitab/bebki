<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    use Resp;

    public function __construct(private readonly BlogService $blogService, private readonly UploadService $uploadService)
    {
    }

    public function index(Request $request)
    {
        $blogs = $this->blogService->findMany($request->search);
        return $this->apiResponseSuccess($blogs);
    }

    public function show(Request $request, string $slug)
    {
        $validator = Validator::make(
            [
                'slug' => $slug,
            ],
            [
                'slug' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $blog = $this->blogService->findOne($slug);
        if (!is_null($blog)) {
            return $this->apiResponseSuccess($blog);
        }
        return $this->apiResponseFail('blog not found');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make(
            [
                'slug' => $request->slug,
                'title' => $request->title,
                'small_description' => $request->small_description,
                'text' => $request->text,
                'category' => $request->category,
            ],
            [
                'slug' => ['required'],
                'title' => ['required'],
                'small_description' => ['required'],
                'text' => ['required'],
                'category' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $blog = $this->blogService->create(
            $user,
            $request->slug,
            $request->title,
            $request->small_description,
            $request->text,
            $request->category,
            $request->image_urls,
        );

        if ($blog) {
            return $this->apiResponseSuccess(['data' => $blog]);
        }
        return $this->apiResponseFail('Something went wrong');
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $validator = Validator::make(
            [
                'slug' => $request->slug,
                'title' => $request->title,
                'small_description' => $request->small_description,
                'text' => $request->text,
                'category' => $request->category,
            ],
            [
                'slug' => ['required'],
                'title' => ['required'],
                'small_description' => ['required'],
                'text' => ['required'],
                'category' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $updateBlog = $this->blogService->update(
            $id,
            $user,
            $request->slug,
            $request->title,
            $request->small_description,
            $request->text,
            $request->category,
            $request->image_urls,
        );

        if ($updateBlog) {
            return $this->apiResponseSuccess(['data' => $updateBlog]);
        }
        return $this->apiResponseFail('Something went wrong');
    }

    public function delete(Request $request, $id)
    {
        $deleteBlog = $this->blogService->delete($id);
        if ($deleteBlog) {
            return $this->apiResponseSuccess(['data' => $deleteBlog]);
        }
        return $this->apiResponseFail('Something went wrong');
    }

    public function upload_images(Request $request)
    {
        $validator = Validator::make(
            [
                'images' => $request->file('images'),
            ],
            [
                'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadBlogImages($images, $user)]);
    }
}
