<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Models\Products\Product;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

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

    public function getByCompany(string $companyId): array
    {
        // Find MongoDB product IDs belonging to this artisan's company
        $productIds = Product::where('created_by.id', $companyId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($productIds)) {
            return [];
        }

        // Find MySQL orders containing any of those product_ids in the JSON items array
        $orders = $this->orderRepository->findByProductIds($productIds);

        return $orders->map(function ($order) {
            $arr = $order->toArray();
            // Strip personal data — artisan should only see buyer name
            if (isset($arr['shipping']) && is_array($arr['shipping'])) {
                $arr['shipping'] = [
                    'full_name' => $arr['shipping']['full_name'] ?? '',
                ];
            }
            return $arr;
        })->values()->toArray();
    }

    public function artisanUpdateStatus(string $orderId, string $companyId): bool
    {
        // Verify the order contains a product belonging to this artisan
        $productIds = Product::where('created_by.id', $companyId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($productIds)) {
            return false;
        }

        $order = $this->orderRepository->findById($orderId);
        if (!$order) {
            return false;
        }

        // Ensure the order is still pending
        if ($order->status !== OrderStatus::PENDING) {
            return false;
        }

        // Verify ownership — at least one order item belongs to this artisan
        $items = is_array($order->items) ? $order->items : json_decode($order->items ?? '[]', true);
        $owns = collect($items)->contains(fn($item) => in_array($item['product_id'] ?? '', $productIds));
        if (!$owns) {
            return false;
        }

        DB::table('orders')->where('id', $orderId)->update([
            'status'     => OrderStatus::READY_TO_SHIP,
            'updated_at' => now(),
        ]);

        return true;
    }
}
