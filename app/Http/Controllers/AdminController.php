<?php

namespace App\Http\Controllers;

use App\Constants\ShopStatus;
use App\Services\CategoryService;
use App\Services\UserInformationService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // If approving, stamp verified_at on the company
        if ($request->status === ShopStatus::VERIFIED) {
            DB::table('companies')
                ->where('id', $companyId)
                ->update(['verified_at' => now()]);
        }

        return $this->apiResponseSuccess(['company_id' => $companyId, 'shop_status' => $request->status]);
    }

    /**
     * GET /admin/orders
     * Returns all orders for the admin panel.
     */
    public function orders()
    {
        $orders = DB::table('orders')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->apiResponseSuccess(['data' => $orders]);
    }

    /**
     * POST /admin/orders/{order_id}/status
     */
    public function updateOrderStatus(Request $request, string $orderId)
    {
        $valid = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($request->status, $valid, true)) {
            return $this->apiResponseFail('Invalid status.');
        }
        $updated = DB::table('orders')->where('id', $orderId)->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);
        if (!$updated) {
            return $this->apiResponseFail('Order not found.');
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
