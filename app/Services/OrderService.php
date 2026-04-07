<?php

namespace App\Services;

use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(private readonly OrderRepository $orderRepository) {}

    public function place(string $userId, array $items, array $shipping, float $total): array
    {
        // Store only product_id + qty per item
        $cleanItems = array_map(fn($i) => [
            'product_id' => $i['product_id'],
            'qty'        => (int) ($i['qty'] ?? 1),
        ], $items);

        $order = $this->orderRepository->create($userId, $cleanItems, $shipping, $total);

        return $order->toArray();
    }

    public function getByUser(string $userId): array
    {
        return $this->orderRepository->findByUserId($userId)->toArray();
    }
}
