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
}
