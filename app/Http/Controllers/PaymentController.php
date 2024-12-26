<?php

namespace App\Http\Controllers;

use App\DTO\PaymentDTO;
use App\Services\StripePaymentService;
use App\Traits\Resp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    use Resp;
    public function __construct(private readonly StripePaymentService $stripeService) {}


    public function createStripePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'transaction_id' => 'nullable|string',
            'order_id' => 'nullable|string',
            'customer_email' => 'nullable|string|email|max:255',
            'customer_name' => 'nullable|string|max:255',
            'payment_provider' => 'nullable|string|max:255',
            'provider_transaction_id' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:PENDING,CREATED,PAID,FAILED',
            'status_description' => 'nullable|string',
            'error_code' => 'nullable|string|max:255',
            'error_message' => 'nullable|string',
            'cancelled_at' => 'nullable|date',
            'refunded_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'payment_data' => 'nullable|array',
            'total_amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'invoice_url' => 'nullable|string|url',
        ]);
        $user = auth()->user();
        // Create PaymentDTO from validated data
        $paymentDTO = new PaymentDTO(
            userId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->header('User-Agent'),
            customerEmail: $user->username ?? null,
            customerName: $user?->information?->first_name.' '.$user?->information?->last_name ?? null,
            paymentProvider: 'stripe',
            paymentData: $validated['payment_data'] ?? [],
            totalAmount: $validated['total_amount'],
            currency: 'GEL',
        );

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
