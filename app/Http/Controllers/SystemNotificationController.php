<?php

namespace App\Http\Controllers;

use App\Services\SystemNotificationService;
use App\Traits\Resp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SystemNotificationController extends Controller
{
    use Resp;
    public function __construct(
        private readonly SystemNotificationService $systemNotificationService
    ) {
    }

    /**
     * Get paginated list of notifications
     */
    public function findMany(Request $request)
    {
        $notifications = $this->systemNotificationService->findMany(
            recipientId: $request->user()->id
        );

       return $this->apiResponseSuccess($notifications);
    }

    /**
     * Get a single notification
     */
    public function show(string $id): JsonResponse
    {
        return $this->apiResponseSuccess($this->systemNotificationService->findOne($id));
    }

    /**
     * Delete a notification
     */
    public function delete(Request $request, $notification_id)
    {

        $validator = Validator::make(
            [
                'notification_id' => $notification_id,
            ],
            [
                'notification_id' => ['required'],
            ]
        );
        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        $notification = $this->systemNotificationService->delete($notification_id);

        if ($notification) {
            return $this->apiResponseSuccess(['data' => $notification]);
        }
        return $this->apiResponseFail('Notification Already Deleted');
    }
}
