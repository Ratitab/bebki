<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Constants\ShopStatus;
use App\Mail\DynamicEmail;
use App\Services\CategoryService;
use App\Services\UserInformationService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    use Resp;

    public function __construct(
        private readonly UserInformationService $userInformationService,
        private readonly CategoryService $categoryService,
    ) {
    }

    /**
     * GET /admin/companies
     * Returns all companies with their information + shop_status from user_information.
     */
    public function companies()
    {
        $rows = DB::table('company_users')
            ->join('companies', 'company_users.company_id', '=', 'companies.id')
            ->leftJoin('company_information', function ($join) {
                $join->on('companies.id', '=', 'company_information.company_id')
                    ->whereNull('company_information.deleted_at');
            })
            ->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->leftJoin('user_information as ui_status', function ($join) {
                $join->on('company_users.user_id', '=', 'ui_status.user_id')
                    ->where('ui_status.user_information_type_id', 6)
                    ->whereNull('ui_status.deleted_at');
            })
            ->select([
                'companies.id as company_id',
                'companies.company_type_id',
                'companies.created_at',
                'company_users.user_id',
                'company_information.value as info_value',
                'company_information_types.name as info_type',
                'ui_status.value as shop_status',
            ])
            ->orderBy('companies.created_at', 'desc')
            ->get();

        // Group by company_id
        $grouped = [];
        foreach ($rows as $row) {
            $id = $row->company_id;
            if (!isset($grouped[$id])) {
                $grouped[$id] = [
                    'company_id'      => $id,
                    'user_id'         => $row->user_id,
                    'company_type_id' => $row->company_type_id,
                    'created_at'      => $row->created_at,
                    'shop_status'     => $row->shop_status,
                    'information'     => [],
                ];
            }
            if ($row->info_type) {
                $grouped[$id]['information'][$row->info_type] = $row->info_value;
            }
        }

        return $this->apiResponseSuccess(['data' => array_values($grouped)]);
    }

    /**
     * POST /admin/company/{company_id}/status
     * Body: { "status": "verified" | "rejected" | "pending" }
     */
    public function updateStatus(Request $request, string $companyId)
    {
        $validator = Validator::make(
            ['status' => $request->status],
            ['status' => ['required', 'in:' . implode(',', ShopStatus::ALL)]]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $userId = DB::table('company_users')
            ->where('company_id', $companyId)
            ->value('user_id');

        if (!$userId) {
            return $this->apiResponseFail('Company not found.');
        }

        $this->userInformationService->updateShopStatus($userId, $request->status);

        $artisanEmail = DB::table('users')->where('id', $userId)->value('username');
        $frontendUrl  = rtrim(config('app.frontend_url'), '/');

        if ($request->status === ShopStatus::VERIFIED) {
            DB::table('companies')
                ->where('id', $companyId)
                ->update(['verified_at' => now()]);

            if ($artisanEmail && filter_var($artisanEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($artisanEmail)->send(new DynamicEmail(
                    'emails.artisan-verified',
                    ['email' => $artisanEmail, 'dashboard_link' => $frontendUrl . '/my-shop'],
                    'ოსტატის ანგარიში დამტკიცებულია — ბებკი'
                ));
            }
        }

        if ($request->status === ShopStatus::REJECTED) {
            if ($artisanEmail && filter_var($artisanEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($artisanEmail)->send(new DynamicEmail(
                    'emails.artisan-rejected',
                    ['email' => $artisanEmail, 'account_link' => $frontendUrl . '/account-details'],
                    'ოსტატის ანგარიში უარყოფილია — ბებკი'
                ));
            }
        }

        return $this->apiResponseSuccess(['company_id' => $companyId, 'shop_status' => $request->status]);
    }

    /**
     * GET /admin/orders
     * Returns all orders for the admin panel.
     */
    public function orders()
    {
        $orders = DB::table('orders')->orderBy('created_at', 'desc')->get();

        // Collect all product_ids from all orders for MongoDB lookup
        $allProductIds = [];
        foreach ($orders as $order) {
            $items = json_decode($order->items ?? '[]', true);
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $allProductIds[] = $item['product_id'];
                }
            }
        }

        // Look up company_id per product in MongoDB
        $productCompanyMap = [];
        if (!empty($allProductIds)) {
            \App\Models\Products\Product::whereIn('_id', array_unique($allProductIds))
                ->get(['_id', 'created_by'])
                ->each(function ($product) use (&$productCompanyMap) {
                    $pid       = (string) $product->id;
                    $companyId = $product->created_by['id'] ?? null;
                    if ($companyId) {
                        $productCompanyMap[$pid] = (string) $companyId;
                    }
                });
        }

        // Look up pickup addresses for the found companies
        $pickupAddressMap = [];
        $allCompanyIds    = array_unique(array_values($productCompanyMap));
        if (!empty($allCompanyIds)) {
            DB::table('company_information')
                ->join('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
                ->whereIn('company_information.company_id', $allCompanyIds)
                ->where('company_information_types.name', 'pickup_address')
                ->whereNull('company_information.deleted_at')
                ->select('company_information.company_id', 'company_information.value')
                ->get()
                ->each(function ($row) use (&$pickupAddressMap) {
                    $pickupAddressMap[$row->company_id] = $row->value;
                });
        }

        $result = $orders->map(function ($order) use ($productCompanyMap, $pickupAddressMap) {
            $items           = json_decode($order->items ?? '[]', true);
            $order->items    = $items;
            $order->shipping = json_decode($order->shipping ?? '{}', true);

            // One pickup address entry per unique artisan in this order
            $seen           = [];
            $artisanPickups = [];
            foreach ($items as $item) {
                $pid       = $item['product_id'] ?? '';
                $companyId = $productCompanyMap[$pid] ?? null;
                if ($companyId && !isset($seen[$companyId]) && isset($pickupAddressMap[$companyId])) {
                    $artisanPickups[] = $pickupAddressMap[$companyId];
                    $seen[$companyId] = true;
                }
            }
            $order->artisan_pickup_addresses = $artisanPickups;

            return $order;
        });

        return $this->apiResponseSuccess(['data' => $result]);
    }

    /**
     * POST /admin/orders/{order_id}/status
     */
    public function updateOrderStatus(Request $request, string $orderId)
    {
        $valid = ['pending', 'ready_to_ship', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($request->status, $valid, true)) {
            return $this->apiResponseFail('Invalid status.');
        }
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return $this->apiResponseFail('Order not found.');
        }

        DB::table('orders')->where('id', $orderId)->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);

        $notifyStatuses = [OrderStatus::SHIPPED, OrderStatus::DELIVERED];
        if (in_array($request->status, $notifyStatuses, true)) {
            $shipping = is_array($order->shipping)
                ? $order->shipping
                : json_decode($order->shipping ?? '{}', true);

            $buyerEmail  = $shipping['email'] ?? null;
            $frontendUrl = rtrim(config('app.frontend_url'), '/');

            if ($buyerEmail && filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
                $shortId = strtoupper(substr($orderId, 0, 8));

                if ($request->status === OrderStatus::SHIPPED) {
                    Mail::to($buyerEmail)->send(new DynamicEmail(
                        'emails.order-shipped',
                        [
                            'email'       => $buyerEmail,
                            'order_id'    => $shortId,
                            'orders_link' => $frontendUrl . '/my-orders',
                        ],
                        'შეკვეთა გაიგზავნა — ბებკი'
                    ));
                }

                if ($request->status === OrderStatus::DELIVERED) {
                    Mail::to($buyerEmail)->send(new DynamicEmail(
                        'emails.order-delivered',
                        [
                            'email'        => $buyerEmail,
                            'order_id'     => $shortId,
                            'support_link' => 'mailto:info@bebki.ge',
                        ],
                        'შეკვეთა დასრულებულია — ბებკი'
                    ));
                }
            }
        }

        return $this->apiResponseSuccess(['id' => $orderId, 'status' => $request->status]);
    }

    /**
     * GET /admin/categories/requested
     * Returns all categories with type_id = 2 (user-requested, pending approval).
     */
    public function requestedCategories()
    {
        return $this->apiResponseSuccess(['data' => $this->categoryService->getRequested()]);
    }

    /**
     * POST /admin/categories/{id}/approve
     * Body: { "type_id": 0|1, "parent_id": <optional int> }
     */
    public function approveCategory(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'type_id'   => 'required|in:0,1',
            'parent_id' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $ok = $this->categoryService->approveCategory(
            $id,
            (int) $request->type_id,
            (int) ($request->parent_id ?? 0)
        );

        if (!$ok) {
            return $this->apiResponseFail('Category not found or already approved.');
        }

        return $this->apiResponseSuccess(['id' => $id, 'type_id' => $request->type_id]);
    }

    /**
     * DELETE /admin/categories/{id}/dismiss
     * Soft-deletes a requested category without approving it.
     */
    public function dismissCategory(int $id)
    {
        \App\Models\Category::where('id', $id)->where('type_id', 2)->delete();

        return $this->apiResponseSuccess(['id' => $id]);
    }
}
