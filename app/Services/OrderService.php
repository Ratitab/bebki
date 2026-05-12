<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Mail\DynamicEmail;
use App\Models\Products\Product;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

        $this->notifyArtisans($order->id, $cleanItems, $total);
        $this->notifyBuyer($order->id, $shipping, $cleanItems, $total);

        return $order->toArray();
    }

    private function notifyBuyer(string $orderId, array $shipping, array $items, float $total): void
    {
        $email = $shipping['email'] ?? null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) return;

        $itemsCount  = array_sum(array_column($items, 'qty'));
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        Mail::to($email)->send(new DynamicEmail(
            'emails.order-confirmed',
            [
                'email'            => $email,
                'buyer_name'       => $shipping['full_name'] ?? '',
                'order_id'         => strtoupper(substr($orderId, 0, 8)),
                'items_count'      => $itemsCount,
                'total'            => number_format($total, 2),
                'shipping_name'    => $shipping['full_name'] ?? '',
                'shipping_address' => $shipping['address'] ?? '',
                'shipping_city'    => $shipping['city'] ?? '',
                'shipping_country' => $shipping['country'] ?? '',
                'orders_link'      => $frontendUrl . '/my-orders',
            ],
            'შეკვეთა დადასტურებულია — ბებკი'
        ));
    }

    private function notifyArtisans(string $orderId, array $items, float $total): void
    {
        $productIds = array_column($items, 'product_id');
        if (empty($productIds)) return;

        // Map product_id → company_id from MongoDB
        $companyIds = Product::whereIn('_id', $productIds)
            ->get(['created_by'])
            ->pluck('created_by.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($companyIds)) return;

        $frontendUrl  = rtrim(config('app.frontend_url'), '/');
        $itemsCount   = array_sum(array_column($items, 'qty'));

        foreach ($companyIds as $companyId) {
            $userId = DB::table('company_users')->where('company_id', $companyId)->value('user_id');
            if (!$userId) continue;

            $email = DB::table('users')->where('id', $userId)->value('username');
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            Mail::to($email)->send(new DynamicEmail(
                'emails.new-order',
                [
                    'email'       => $email,
                    'order_id'    => strtoupper(substr($orderId, 0, 8)),
                    'items_count' => $itemsCount,
                    'total'       => number_format($total, 2),
                    'orders_link' => $frontendUrl . '/my-shop',
                ],
                'ახალი შეკვეთა — ბებკი'
            ));
        }
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
