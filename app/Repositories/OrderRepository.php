<?php

namespace App\Repositories;

use App\Constants\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderRepository
{
    public function __construct(private readonly Order $orderModel) {}

    public function create(string $userId, array $items, array $shipping, float $total): Order
    {
        return $this->orderModel->create([
            'id'           => (string) Str::uuid(),
            'user_id'      => $userId,
            'order_number' => strtoupper(Str::random(3)) . '-' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT),
            'status'       => OrderStatus::PENDING,
            'items'        => $items,
            'shipping'     => $shipping,
            'total'        => $total,
            'currency'     => 'GEL',
        ]);
    }

    public function findByUserId(string $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orderModel
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(string $id): ?Order
    {
        return $this->orderModel->find($id);
    }
}
