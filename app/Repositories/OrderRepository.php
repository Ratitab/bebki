<?php

namespace App\Repositories;

use App\Constants\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderRepository
{
    public function __construct(private readonly Order $orderModel) {}

    private function generateOrderNumber(): string
    {
        return strtoupper(Str::random(2)) . '-' . str_pad((string) random_int(0, 99999), 4, '0', STR_PAD_LEFT);
    }

    public function create(string $userId, array $items, array $shipping, float $total): Order
    {
        return $this->orderModel->create([
            'id'           => (string) Str::uuid(),
            'user_id'      => $userId,
            'order_number' => $this->generateOrderNumber(),
            'status'       => OrderStatus::PENDING,
            'items'        => $items,
            'shipping'     => $shipping,
            'total'        => $total,
            'currency'     => 'GEL',
        ]);
    }

    public function createWithPayment(
        string $id,
        string $userId,
        array $items,
        array $shipping,
        float $total,
        string $paymentStatus
    ): Order {
        return $this->orderModel->create([
            'id'             => $id,
            'user_id'        => $userId,
            'order_number'   => $this->generateOrderNumber(),
            'status'         => OrderStatus::PENDING,
            'payment_status' => $paymentStatus,
            'items'          => $items,
            'shipping'       => $shipping,
            'total'          => $total,
            'currency'       => 'GEL',
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

    public function findByProductIds(array $productIds): \Illuminate\Database\Eloquent\Collection
    {
        return $this->orderModel
            ->where('payment_status', \App\Constants\PaymentStatus::PAID)
            ->where(function ($query) use ($productIds) {
                foreach ($productIds as $pid) {
                    // PostgreSQL: cast json → jsonb and use containment operator
                    $query->orWhereRaw(
                        'items::jsonb @> ?::jsonb',
                        [json_encode([['product_id' => $pid]])]
                    );
                }
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
