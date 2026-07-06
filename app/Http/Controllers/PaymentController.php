<?php

namespace App\Http\Controllers;

use App\Constants\PaymentStatus;
use App\DTO\PaymentDTO;
use App\Rules\ValidCompanyBelongsUser;
use App\Services\FlittService;
use App\Services\LimitService;
use App\Services\OrderService;
use App\Services\StripePaymentService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    use Resp;

    public function __construct(
        private readonly StripePaymentService $stripeService,
        private readonly LimitService $limitService,
        private readonly FlittService $flittService,
        private readonly OrderService $orderService
    ) {}


    public function flittCallback(Request $request): JsonResponse
    {
        // DEBUG: log raw payload to diagnose callback issues — remove after fix
        $payload = $request->json()->all() ?: $request->all();
        Log::info('FLITT_DEBUG payload', [
            'order_id'     => $payload['order_id'] ?? null,
            'order_status' => $payload['order_status'] ?? null,
            'payment_id'   => $payload['payment_id'] ?? null,
            'sig_hint'     => $payload['response_signature_string'] ?? 'not present (live mode)',
            'sig_received' => $payload['signature'] ?? null,
        ]);

        if (!$this->flittService->verifyCallback($payload)) {
            Log::warning('FLITT_DEBUG sig FAILED', [
                'order_id'  => $payload['order_id'] ?? null,
                'sig_hint'  => $payload['response_signature_string'] ?? null,
            ]);
            return response()->json(['status' => 'invalid_signature'], 400);
        }

        $orderId       = $payload['order_id'] ?? null;
        $flittStatus   = $payload['order_status'] ?? '';
        $flittPayId    = (string) ($payload['payment_id'] ?? '');

        Log::info('FLITT_DEBUG sig OK', ['order_id' => $orderId, 'flitt_status' => $flittStatus]);

        if (!$orderId) {
            return response()->json(['status' => 'missing_order_id'], 400);
        }

        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            Log::error('FLITT_DEBUG order not found', ['order_id' => $orderId]);
            return response()->json(['status' => 'order_not_found'], 200);
        }

        $statusMap = [
            'approved'   => PaymentStatus::PAID,
            'declined'   => PaymentStatus::FAILED,
            'expired'    => PaymentStatus::EXPIRED,
            'reversed'   => PaymentStatus::REVERSED,
            'blocked'    => PaymentStatus::BLOCKED,
        ];

        $newStatus = $statusMap[$flittStatus] ?? PaymentStatus::FAILED;
        Log::info('FLITT_DEBUG updating status', ['order_id' => $orderId, 'new_status' => $newStatus]);
        $this->orderService->updatePaymentStatus($orderId, $newStatus, $flittPayId);

        if ($newStatus === PaymentStatus::PAID) {
            $items    = json_decode($order->items ?? '[]', true);
            $shipping = json_decode($order->shipping ?? '{}', true);
            $this->orderService->sendConfirmationEmails($orderId, $items, $shipping, (float) $order->total);
            $this->orderService->createArtisanStatuses($orderId, $items);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function createStripePayment(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validator = Validator::make(
            [
                'package' => $request->package,
                'company_id' => $request->company_id,
                'type' => $request->type,
            ],
            [
                'package' => ['required', 'regex:/^(custom_\d+|small|medium|premium|pro)$/'],
                'company_id' => $request->company_id ? ['nullable', new ValidCompanyBelongsUser($user->id)] : ['nullable'],
                'type' => ['required', 'in:individual,shop,pawnshop,stock_exchange'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }


        $packages = config('services.pearls');
        $boughtLimits = null;
        $pack = null;
        if (preg_match('/^custom_(\d+)$/', $request->package, $matches)) {
            $boughtLimits = $matches[1];
            $price = (int)$matches[1] * ($matches[1] > 10 ? 10 : 15);
            $pack = explode('_',$request->package)[0];
        } else {
            $price = $packages[$request->package]['price'];
        }

        $payload = [
            'createdBy' => ['id' => $request->company_id ? $request->company_id : $user->id, 'type' => $request->type],
            'user' => ['id' => $user->id, 'information' => ['first_name' => $user->information['first_name'], 'last_name' => $user->information['last_name']]],
            'price' => $price * 100,
            'package' => $packages[$pack ?? $request->package]['package'],
            'bought_limits' => $boughtLimits ?? $packages[$pack ?? $request->package]['limit_count'],
            'limit_count' => $boughtLimits ?? $packages[$pack ?? $request->package]['limit_count'],
            'limit_for' => $request->type
        ];

        $request->merge([
            'user_id' => $user->id,
            'payment_data'=>$payload,'currency'=>'GEL',
            'customer_email'=>$user->username,
            'customer_name' =>$payload['user']['information']['first_name'].' '.$payload['user']['information']['last_name'],
            'total_amount'=>$payload['price'],
            'order_id'=>str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'payment_provider'=>'stripe'
        ]);

        $paymentDTO = PaymentDTO::fromRequest($request);

        $response = $this->stripeService->payment($paymentDTO);

        return $this->apiResponseSuccess($response);
    }
    /**
     * Handle Stripe webhook events.
     *
     * @param Request $request
     */
    public function handleWebhook(Request $request)
    {

        $payload = json_decode($request->getContent(),true);
        if($payload['type'] === 'charge.succeeded' && $payload['data']['object']['paid'] === true){
            $payment = $this->stripeService->updatePaymentStatus($payload['data']['object']['metadata']['order_id'],'PAID');
            if($payment){
                $internalPayload = $this->stripeService->findByOrderId($payload['data']['object']['metadata']['order_id']);
                $activateRequest = new Request(json_decode($internalPayload['payment_data'],true));
                $this->activate_limits($activateRequest);
            }
        }
        $this->stripeService->webhookResponse($payload['data']['object']['metadata']['order_id'],$payload);
        return true;
    }

    public function findManyByUserId()
    {
        return $this->apiResponseSuccess($this->stripeService->findManyByUserId(auth()->id()));
    }

    public function activate_limits(Request $request)
    {
        $rules = [
            'createdBy' => ['required', 'array'],
            'createdBy.id' => ['required'],
            'createdBy.type' => ['required', 'in:individual,shop,pawnshop'],
            'user' => ['required', 'array'],
            'user.id' => ['required'],
            'price' => ['required', 'numeric'],
            'package' => ['required', 'string'],
            'limit_count' => ['required', 'numeric'],
            'limit_for' => ['required', 'in:individual,shop,pawnshop'],
        ];

        // Add ValidCompanyBelongsUser rule only for shop/pawnshop types
        if (in_array($request->input('createdBy.type'), ['shop', 'pawnshop'])) {
            $rules['createdBy.id'][] = new ValidCompanyBelongsUser($request->input('user.id'));
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }
        return $this->apiResponseSuccess(['data' => $this->limitService->buyLimits($request->input('createdBy'),
            (object)$request->input('user'),
            $request->input('price'),
            $request->input('package'),
            $request->input('bought_limits'),
            $request->input('limit_count'),
            $request->input('limit_for'))]);
    }
}
