<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Services\FlittService;
use App\Services\OrderService;
use App\Services\UserInformationService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use Resp;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly FlittService $flittService,
        private readonly UserInformationService $userInfoService
    ) {}

    private function serviceFee(float $total): float
    {
        return match (true) {
            $total <= 50  => 1,
            $total <= 100 => 2,
            $total <= 150 => 3,
            $total <= 200 => 4,
            $total <= 250 => 5,
            $total <= 300 => 6,
            $total <= 350 => 7,
            $total <= 400 => 8,
            $total <= 450 => 9,
            default       => 10,
        };
    }

    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.title'      => ['nullable', 'string'],
            'items.*.image'      => ['nullable', 'string'],
            'items.*.variant'    => ['nullable', 'array'],
            'total'              => ['required', 'numeric', 'min:0'],
            'shipping'           => ['required', 'array'],
            'shipping.full_name' => ['required', 'string'],
            'shipping.email'     => ['required', 'email'],
            'shipping.address'   => ['required', 'string'],
            'shipping.building'  => ['nullable', 'string'],
            'shipping.entrance'  => ['nullable', 'string'],
            'shipping.floor'     => ['nullable', 'string'],
            'shipping.apartment' => ['nullable', 'string'],
            'shipping.city'      => ['required', 'string'],
            'shipping.country'   => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        // Verify total server-side (never trust frontend total)
        $serverTotal = $this->orderService->calculateTotal($request->items);
        if (abs($serverTotal - (float) $request->total) > 0.05) {
            return $this->apiResponseFail('Price mismatch — please refresh and try again.');
        }

        // Generate the order ID before calling Flitt (used as our reference)
        $orderId     = (string) Str::uuid();
        $fee         = $this->serviceFee($serverTotal);
        $grandTotal  = $serverTotal + $fee;
        $amountTetri = (int) round($grandTotal * 100);
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        try {
            $flitt = $this->flittService->getCheckoutToken([
                'order_id'            => $orderId,
                'order_desc'          => 'ბებკი',
                'amount'              => $amountTetri,
                'currency'            => 'GEL',
                'response_url'        => $frontendUrl . '/checkout/result',
                'server_callback_url' => config('app.url') . '/api/payment/flitt/callback',
            ]);
        } catch (\Throwable $e) {
            Log::error('Flitt initiate failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return $this->apiResponseFail('Payment service unavailable. Please try again later.');
        }

        // Only persist to DB after Flitt confirms the checkout session
        $order = $this->orderService->createPendingOrder(
            auth()->id(),
            $orderId,
            $request->items,
            $request->shipping,
            $grandTotal
        );

        // Fill any missing address fields on the user profile — never overwrites existing values
        $shipping = $request->shipping;
        $this->userInfoService->fillMissingAddressInfo(auth()->id(), [
            'phone'     => $shipping['phone']     ?? null,
            'address'   => $shipping['address']   ?? null,
            'building'  => $shipping['building']  ?? null,
            'entrance'  => $shipping['entrance']  ?? null,
            'floor'     => $shipping['floor']     ?? null,
            'apartment' => $shipping['apartment'] ?? null,
            'city'      => $shipping['city']      ?? null,
            'country'   => $shipping['country']   ?? null,
        ]);

        return $this->apiResponseSuccess([
            'data' => [
                'checkout_url' => $flitt['checkout_url'],
                'token'        => (function($url) {
                    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $q);
                    return $q['token'] ?? null;
                })($flitt['checkout_url']),
                'order_id'     => $orderId,
                'order_number' => $order->order_number,
                'total'        => $grandTotal,
                'fee'          => $fee,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.qty'        => ['required', 'integer', 'min:1'],
            'items.*.title'      => ['nullable', 'string'],
            'items.*.image'      => ['nullable', 'string'],
            'items.*.variant'    => ['nullable', 'array'],
            'total'              => ['required', 'numeric', 'min:0'],
            'shipping'           => ['required', 'array'],
            'shipping.full_name' => ['required', 'string'],
            'shipping.email'     => ['required', 'email'],
            'shipping.address'   => ['required', 'string'],
            'shipping.building'  => ['nullable', 'string'],
            'shipping.entrance'  => ['nullable', 'string'],
            'shipping.floor'     => ['nullable', 'string'],
            'shipping.apartment' => ['nullable', 'string'],
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
