<?php

namespace App\Http\Controllers;

use App\DTO\PaymentDTO;
use App\Rules\ValidCompanyBelongsUser;
use App\Services\StripePaymentService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    use Resp;
    public function __construct(private readonly StripePaymentService $stripeService) {}


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
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );

            // Handle the event type
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->stripeService->updatePaymentStatus($paymentIntent->id);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    Log::error('Payment failed', ['payment_intent' => $paymentIntent]);
                    break;

                // Add more cases as needed for other events
                default:
                    Log::info('Unhandled event type', ['type' => $event->type]);
            }

            return response()->json(['status' => 'success']);

        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['error' => 'Invalid signature'], 400);
        }
    }
}
