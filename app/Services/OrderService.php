<?php

namespace App\Services;

use App\Constants\OrderStatus;
use App\Constants\PaymentStatus;
use App\Mail\DynamicEmail;
use App\Models\Order;
use App\Models\Products\Product;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly OrderRepository $orderRepository) {}

    public function calculateTotal(array $items): float
    {
        $productIds = array_column($items, 'product_id');
        $products   = Product::whereIn('_id', $productIds)->get(['_id', 'price', 'variants'])->keyBy('_id');

        $total = 0.0;
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) continue;

            $basePrice = (float) ($product->price ?? 0);
            $modifier  = 0.0;

            if (!empty($item['variant']) && !empty($product->variants)) {
                foreach ($product->variants as $variantGroup) {
                    foreach ($variantGroup['options'] ?? [] as $option) {
                        $matchKey = array_key_first($item['variant']);
                        if (
                            isset($item['variant'][$matchKey]) &&
                            ($option['value'] ?? '') === $item['variant'][$matchKey]
                        ) {
                            $modifier = (float) ($option['price_modifier'] ?? 0);
                            break 2;
                        }
                    }
                }
            }

            $total += ($basePrice + $modifier) * (int) ($item['qty'] ?? 1);
        }

        return round($total, 2);
    }

    public function createPendingOrder(
        string $userId,
        string $orderId,
        array $items,
        array $shipping,
        float $total
    ): Order {
        $cleanItems = array_map(function ($i) {
            $item = [
                'product_id' => $i['product_id'],
                'qty'        => (int) ($i['qty'] ?? 1),
            ];
            if (!empty($i['title']))   $item['title']   = $i['title'];
            if (!empty($i['image']))   $item['image']   = $i['image'];
            if (!empty($i['variant'])) $item['variant'] = $i['variant'];
            return $item;
        }, $items);

        return $this->orderRepository->createWithPayment(
            $orderId,
            $userId,
            $cleanItems,
            $shipping,
            $total,
            PaymentStatus::PENDING
        );
    }

    public function updatePaymentStatus(string $orderId, string $status, ?string $flittPaymentId = null): void
    {
        $data = ['payment_status' => $status, 'updated_at' => now()];
        if ($flittPaymentId !== null) {
            $data['flitt_payment_id'] = $flittPaymentId;
        }
        DB::table('orders')->where('id', $orderId)->update($data);
    }

    public function sendConfirmationEmails(string $orderId, array $items, array $shipping, float $total): void
    {
        $this->notifyArtisans($orderId, $items, $total);
        $this->notifyBuyer($orderId, $shipping, $items, $total);
    }

    /**
     * Create one status row per artisan shop involved in the order.
     * Called when payment is confirmed — these rows are what artisans act on.
     */
    public function createArtisanStatuses(string $orderId, array $items): void
    {
        $companyIds = $this->resolveCompanyIds($items);
        if (empty($companyIds)) return;

        $now  = now();
        $rows = array_map(fn($cid) => [
            'order_id'   => $orderId,
            'company_id' => $cid,
            'status'     => OrderStatus::PENDING,
            'created_at' => $now,
            'updated_at' => $now,
        ], $companyIds);

        // Safe to use insertOrIgnore — unique constraint on (order_id, company_id)
        DB::table('order_artisan_statuses')->insertOrIgnore($rows);
    }

    public function artisanUpdateStatus(string $orderId, string $companyId): bool
    {
        $productIds = Product::where('created_by.id', $companyId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($productIds)) return false;

        $order = $this->orderRepository->findById($orderId);
        if (!$order) return false;

        // Only paid orders can be moved forward
        if ($order->payment_status !== PaymentStatus::PAID) return false;

        // Already past pending — nothing to do
        if ($order->status !== OrderStatus::PENDING) return false;

        // Verify this artisan has an item in the order
        $items = is_array($order->items) ? $order->items : json_decode($order->items ?? '[]', true);
        $owns  = collect($items)->contains(fn($item) => in_array($item['product_id'] ?? '', $productIds));
        if (!$owns) return false;

        // Flip this artisan's own row
        DB::table('order_artisan_statuses')
            ->where('order_id', $orderId)
            ->where('company_id', $companyId)
            ->update(['status' => OrderStatus::READY_TO_SHIP, 'updated_at' => now()]);

        // If every artisan for this order has confirmed, flip the main order status
        $total     = DB::table('order_artisan_statuses')->where('order_id', $orderId)->count();
        $confirmed = DB::table('order_artisan_statuses')
            ->where('order_id', $orderId)
            ->where('status', OrderStatus::READY_TO_SHIP)
            ->count();

        if ($total > 0 && $confirmed === $total) {
            DB::table('orders')->where('id', $orderId)->update([
                'status'     => OrderStatus::READY_TO_SHIP,
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    public function place(string $userId, array $items, array $shipping, float $total): array
    {
        $cleanItems = array_map(function ($i) {
            $item = [
                'product_id' => $i['product_id'],
                'qty'        => (int) ($i['qty'] ?? 1),
            ];
            if (!empty($i['title']))   $item['title']   = $i['title'];
            if (!empty($i['image']))   $item['image']   = $i['image'];
            if (!empty($i['variant'])) $item['variant'] = $i['variant'];
            return $item;
        }, $items);

        $order = $this->orderRepository->create($userId, $cleanItems, $shipping, $total);

        $this->notifyArtisans($order->id, $cleanItems, $total);
        $this->notifyBuyer($order->id, $shipping, $cleanItems, $total);

        return $order->toArray();
    }

    public function getByUser(string $userId): array
    {
        return $this->orderRepository->findByUserId($userId)->toArray();
    }

    public function getByCompany(string $companyId): array
    {
        $productIds = Product::where('created_by.id', $companyId)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        if (empty($productIds)) return [];

        $orders = $this->orderRepository->findByProductIds($productIds);

        return $orders->map(function ($order) use ($companyId) {
            $arr = $order->toArray();
            if (isset($arr['shipping']) && is_array($arr['shipping'])) {
                $arr['shipping'] = ['full_name' => $arr['shipping']['full_name'] ?? ''];
            }
            // Include this artisan's own confirmation status
            $arr['artisan_status'] = DB::table('order_artisan_statuses')
                ->where('order_id', $order->id)
                ->where('company_id', $companyId)
                ->value('status') ?? OrderStatus::PENDING;
            return $arr;
        })->values()->toArray();
    }

    private function resolveCompanyIds(array $items): array
    {
        $productIds = array_column($items, 'product_id');
        if (empty($productIds)) return [];

        return Product::whereIn('_id', $productIds)
            ->get(['created_by'])
            ->pluck('created_by.id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
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
        $companyIds = $this->resolveCompanyIds($items);
        if (empty($companyIds)) return;

        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $itemsCount  = array_sum(array_column($items, 'qty'));

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
}
