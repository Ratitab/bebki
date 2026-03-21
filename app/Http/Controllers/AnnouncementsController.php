<?php

namespace App\Http\Controllers;

use App\Services\AnnouncementsService;
use App\Services\UploadService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementsController extends Controller
{
    use Resp;

    public function __construct(private readonly AnnouncementsService $announcementsService, private readonly UploadService $uploadService)
    {
    }

    public function index(Request $request)
    {
        $announcements = $this->announcementsService->findMany($request->search);
        return $this->apiResponseSuccess($announcements);
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

        $announcement = $this->announcementsService->findOne($slug);
        if (!is_null($announcement)) {
            return $this->apiResponseSuccess($announcement);
        }
        return $this->apiResponseFail('announcement not found');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make(
            [
                'slug' => $request->slug,
            ],
            [
                'slug' => ['required'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $announcement = $this->announcementsService->create(
            $user,
            $request->slug,
            $request->image_urls,
        );

        if ($announcement) {
            return $this->apiResponseSuccess(['data' => $announcement]);
        }
        return $this->apiResponseFail('Something went wrong');
    }

    // public function update(Request $request, $id)
    // {
    //     $user = auth()->user();

    //     $validator = Validator::make(
    //         [
    //             'slug' => $request->slug,
    //             'title' => $request->title,
    //             'small_description' => $request->small_description,
    //             'text' => $request->text,
    //             'category' => $request->category,
    //         ],
    //         [
    //             'slug' => ['required'],
    //             'title' => ['required'],
    //             'small_description' => ['required'],
    //             'text' => ['required'],
    //             'category' => ['required'],
    //         ]
    //     );

    //     if ($validator->fails()) {
    //         return $this->apiResponseFail($validator->messages());
    //     }

    //     if ($validator->fails()) {
    //         return $this->apiResponseFail($validator->messages());
    //     }

    //     $updateBlog = $this->blogService->update(
    //         $id,
    //         $user,
    //         $request->slug,
    //         $request->title,
    //         $request->small_description,
    //         $request->text,
    //         $request->category,
    //         $request->image_urls,
    //     );

    //     if ($updateBlog) {
    //         return $this->apiResponseSuccess(['data' => $updateBlog]);
    //     }
    //     return $this->apiResponseFail('Something went wrong');
    // }

    public function delete(Request $request, $id)
    {
        $deleteAnnouncements = $this->announcementsService->delete($id);
        if ($deleteAnnouncements) {
            return $this->apiResponseSuccess(['data' => $deleteAnnouncements]);
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
        // aq gvchirdeba check if user is admin

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $images = $request->file('images');
        $user = auth()->user();
        return $this->apiResponseSuccess(['data' => $this->uploadService->uploadAnnouncementImages($images, $user)]);
    }
}
