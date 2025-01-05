<?php

namespace App\Services;

use App\Models\SystemNotifications\SystemNotification;
use App\Repositories\SystemNotificationRepository;
use App\Repositories\UserRepository;
use App\Repositories\CompanyRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SystemNotificationService
{
    public function __construct(
        private readonly SystemNotificationRepository $systemNotificationRepository,
    ) {
    }

    public function findMany($recipientId, $type = null, $status = null)
    {
        $notifications = $this->systemNotificationRepository->findAll($recipientId);

        $notificationIds = $notifications->pluck('id')->toArray();
        $notificationSeen = $this->systemNotificationRepository->findSeenAll($notificationIds);

        // Transform notifications to include sender information
        $notifications->getCollection()->transform(function ($notification) use ($notificationSeen) {
            $notificationExists = $notificationSeen->contains(function ($seenNotification) use ($notification) {
                return $seenNotification->notification_id === $notification->_id;
            });

            $notification->seen = $notificationExists;
            return $notification;
        });

        return $notifications;
    }

    public function findOne($id)
    {
        $notification = $this->systemNotificationRepository->findSingle($id);

        if (!$notification) {
            return null;
        }

        return $notification;
    }

    public function create($title, $message, $type, $notificationFrom, $recipientId)
    {
        return DB::transaction(function () use ($title, $message, $type, $notificationFrom, $recipientId) {
            return $this->systemNotificationRepository->create(
                $title,
                $message,
                $type,
                $notificationFrom,
                $recipientId,
                'pending'
            );
        });
    }

    public function delete($id)
    {
        return $this->systemNotificationRepository->delete($id);
    }
}
