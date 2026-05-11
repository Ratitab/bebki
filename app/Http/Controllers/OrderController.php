<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Services\OrderService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use Resp;

    public function __construct(private readonly OrderService $orderService) {}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'total'              => ['required', 'numeric', 'min:0'],
            'shipping'           => ['required', 'array'],
            'shipping.full_name' => ['required', 'string'],
            'shipping.email'     => ['required', 'email'],
            'shipping.address'   => ['required', 'string'],
            'shipping.city'      => ['required', 'string'],
            'shipping.country'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $order = $this->orderService->place(
            auth()->id(),
            $request->items,
            $request->shipping,
            (float) $request->total
        );

        return $this->apiResponseSuccess(['data' => $order]);
    }

    public function index()
    {
        $orders = $this->orderService->getByUser(auth()->id());
        return $this->apiResponseSuccess(['data' => $orders]);
    }

    public function artisanOrders(Request $request)
    {
        $companyId = $request->query('company_id');
        if (!$companyId) {
            return $this->apiResponseFail('company_id is required.');
        }
        $orders = $this->orderService->getByCompany($companyId);
        return $this->apiResponseSuccess(['data' => $orders]);
    }

    public function artisanUpdateStatus(Request $request, string $orderId)
    {
        $companyId = $request->input('company_id');
        if (!$companyId) {
            return $this->apiResponseFail('company_id is required.');
        }

        $updated = $this->orderService->artisanUpdateStatus($orderId, $companyId);

        if (!$updated) {
            return $this->apiResponseFail('Cannot update this order. It may already be processed or not belong to your shop.');
        }

        return $this->apiResponseSuccess(['id' => $orderId, 'status' => OrderStatus::READY_TO_SHIP]);
    }
}
