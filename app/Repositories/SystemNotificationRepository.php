<?php

namespace App\Repositories;

use App\Models\SystemNotifications\SystemNotification;
use App\Models\SystemNotifications\SystemNotificationSeen;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SystemNotificationRepository
{
    const SOURCE_SYSTEM = 'system';

    public function __construct(
        private readonly SystemNotification $systemNotificationModel,
        private readonly SystemNotificationSeen $systemNotificationSeenModel,
    )
    {
    }

    public function findAll($recipientId, $type = null, $status = null)
    {
        $query = $this->systemNotificationModel->where(function($q) use ($recipientId) {
            $q->where('recipient_id', $recipientId)
                ->orWhere(function($subQuery) {
                    $subQuery->whereNull('recipient_id')
                        ->where('notification_from', self::SOURCE_SYSTEM);
                });
        });

        if ($type) {
            $query = $query->where('type', $type);
        }

        if ($status) {
            $query = $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function findSingle($id)
    {
        return $this->systemNotificationModel->where('_id', $id)->first();
    }

    public function create($title, $message, $type, $notificationFrom, $recipientId, $status = 'pending')
    {
        $notification = new $this->systemNotificationModel;
        return $this->setNotificationAttributes(
            $notification,
            $title,
            $message,
            $type,
            $notificationFrom,
            $recipientId,
            $status
        );
    }

    private function setNotificationAttributes($notification, $title, $message, $type, $notificationFrom, $recipientId, $status)
    {
        if (!isset($notification->notification_id)) {
            $notification->notification_id = str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);;
        }

        $notification->title = $title;
        $notification->message = $message;
        $notification->type = $type;//individual,store,pawnshop,stock_exchange
        $notification->notification_from = $notificationFrom;
        $notification->recipient_id = $recipientId;
        $notification->status = $status;
        $notification->save();
        return $notification;
    }
    public function makeSeen($userId, $notificationId)
    {
        $makeSeen = new $this->systemNotificationSeenModel;
        $makeSeen->user_id = $userId;
        $makeSeen->notification_id = $notificationId;
        $makeSeen->save();
        return $makeSeen;
    }
    public function findSeenAll($id)
    {
        return $this->systemNotificationModel->whereIn('_id', $id)->get();
    }

    public function delete($id)
    {
        return $this->systemNotificationModel->where('_id', $id)->delete();
    }
}
