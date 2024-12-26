<?php

namespace App\Services;

use App\DTO\PaymentDTO;
use App\Repositories\PaymentRepository;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripePaymentService
{
    private PaymentRepository $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Initialize Stripe with the API key.
     */
    private function initialize(): void
    {
        Stripe::setApiKey(config('services.stripe.secret_key'));
    }

    /**
     * Create a payment intent and save the payment record.
     *
     * @param PaymentDTO $paymentDTO
     * @return array
     */
    public function payment(PaymentDTO $paymentDTO): array
    {
        $this->initialize();

        $paymentIntent = PaymentIntent::create([
            'amount' => $paymentDTO->totalAmount, // Amount in cents
            'currency' => $paymentDTO->currency,
            'payment_method_types' => ['card'],
            'metadata' =>  ['order_id' => $paymentDTO->orderId],
        ]);

        $updateData = $paymentDTO->toArray();
        $updateData['providerTransactionId'] = $paymentIntent->id;
        $updateData['status'] = 'CREATED';
        $updatedData = PaymentDTO::fromArray($updateData);
        $this->paymentRepository->create($updatedData);

        return [
            'clientSecret' => $paymentIntent->client_secret,
            'paymentIntentId' => $paymentIntent->id,
        ];
    }

    /**
     * Update the status of a payment to 'PAID' after successful payment.
     *
     * @param string $paymentIntentId
     * @return bool
     */
    public function updatePaymentStatus(string $paymentIntentId): bool
    {
        $payment = $this->paymentRepository->findByProviderTransactionId($paymentIntentId);

        if (!$payment) {
            return false;
        }

        $payment->status = 'PAID';
        return $this->paymentRepository->updateStatus($payment->id, 'PAID');
    }

    /**
     * Refund a payment.
     *
     * @param string $paymentIntentId
     * @param int|null $amount
     * @return bool
     */
    public function refund(string $paymentIntentId, ?int $amount = null): bool
    {
        $this->initialize();

        try {
            $refund = Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount, // Optional: Refund a partial amount
            ]);

            if ($refund->status === 'succeeded') {
                $payment = $this->paymentRepository->findByProviderTransactionId($paymentIntentId);
                if ($payment) {
                    $this->paymentRepository->updateStatus($payment->id, 'REFUNDED');
                }

                return true;
            }
        } catch (\Exception $e) {
            // Log error
            return false;
        }

        return false;
    }
}
