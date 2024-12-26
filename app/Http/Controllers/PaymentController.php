<?php

namespace App\Http\Controllers;

use App\DTO\PaymentDTO;
use App\Rules\ValidCompanyBelongsUser;
use App\Services\LimitService;
use App\Services\StripePaymentService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    use Resp;
    public function __construct(private readonly StripePaymentService $stripeService,private readonly LimitService $limitService) {}


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
                'package' => ['required', 'in:starter,basic,pro,premium'],
                'company_id' => $request->company_id ? ['nullable', new ValidCompanyBelongsUser($user->id)] : ['nullable'],
                'type' => ['required', 'in:individual,shop,pawnshop'],
            ]
        );

        if ($validator->fails()) {
            return $this->apiResponseFail($validator->messages());
        }

        $packages = config('services.pearls');
        $payload = [
            'createdBy' => ['id' => $request->company_id ? $request->company_id : $user->id, 'type' => $request->type],
            'user' => ['id' => $user->id, 'information' => ['first_name' => $user->information['first_name'], 'last_name' => $user->information['last_name']]],
            'price' => $packages[$request->package]['price'],
            'package' => $packages[$request->package]['package'],
            'bought_limits' => $packages[$request->package]['limit_count'],
            'limit_count' => $packages[$request->package]['limit_count'],
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
