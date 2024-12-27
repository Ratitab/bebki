<?php

namespace App\Services;

use App\DTO\PaymentDTO;
use App\Models\Payments\Payment;
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
            'amount' => $paymentDTO->totalAmount,
            'currency' => $paymentDTO->currency,
            'payment_method_types' => ['card'],
            'metadata' =>  ['order_id' => $paymentDTO->orderId,'customer_email' => $paymentDTO->customerEmail,'customer_name'=>$paymentDTO->customerName],
        ]);

        $updateData = $paymentDTO->toArray();
        $updateData['provider_transaction_id'] = $paymentIntent->id;
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
    public function updatePaymentStatus(string $orderId,string $status): bool
    {
        return \DB::transaction(function () use ($orderId,$status) {
        $payment = $this->findByOrderId($orderId);

        if (!$payment) {
            return false;
        }
        if ($status === 'PAID' && is_null($payment->lock_at)) {
            $this->paymentRepository->updateStatus($payment, $status);
            $this->paymentRepository->lockAt($payment);
            return true;
        }

        return false;
        });
    }

    public function findByOrderId(string $orderId): ?Payment
    {
        return $this->paymentRepository->findByOrderId($orderId);
    }

    public function findManyByUserId(string $userId)
    {
        return $this->paymentRepository->findManyByUserId($userId);
    }

    public function webhookResponse(string $orderId,$data): bool
    {
        return \DB::transaction(function () use ($orderId,$data) {
            $payment = $this->findByOrderId($orderId);

            if (!$payment) {
                return false;
            }
            $this->paymentRepository->webhookResponse($payment, $data);
            return true;
        });
    }

}
