<?php

namespace App\Repositories;

use App\DTO\PaymentDTO;
use App\Models\Payments\Payment;
use Illuminate\Support\Str;

class PaymentRepository
{

    public function __construct(
        private readonly Payment $paymentModel,
    ) {
    }

    public function create(PaymentDTO $paymentDTO): Payment
    {
        $payment = new $this->paymentModel();

        $payment->id = Str::uuid();
        $payment->user_id = $paymentDTO->userId;
        $payment->transaction_id = $paymentDTO->transactionId;
        $payment->order_id = $paymentDTO->orderId;
        $payment->ip_address = $paymentDTO->ipAddress;
        $payment->user_agent = $paymentDTO->userAgent;

        $payment->customer_email = $paymentDTO->customerEmail;
        $payment->customer_name = $paymentDTO->customerName;

        $payment->payment_provider = $paymentDTO->paymentProvider;
        $payment->provider_transaction_id = $paymentDTO->providerTransactionId;

        $payment->status = $paymentDTO->status;
        $payment->status_description = $paymentDTO->statusDescription;
        $payment->error_code = $paymentDTO->errorCode;
        $payment->error_message = $paymentDTO->errorMessage;

        $payment->cancelled_at = $paymentDTO->cancelledAt;
        $payment->refunded_at = $paymentDTO->refundedAt;
        $payment->notes = $paymentDTO->notes;

        $payment->payment_data = json_encode($paymentDTO->paymentData);
        $payment->total_amount = $paymentDTO->totalAmount;
        $payment->currency = $paymentDTO->currency;
        $payment->exchange_rate = $paymentDTO->exchangeRate;
        $payment->invoice_url = $paymentDTO->invoiceUrl;

        $payment->save();

        return $payment;
    }

    public function findById(string $id)
    {
        return $this->paymentModel->find($id);
    }

    public function findByOrderId(string $orderId): ?Payment
    {
        return $this->paymentModel->where('order_id', $orderId)->first();
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        return $this->paymentModel->where('transaction_id', $transactionId)->first();
    }

    public function findByProviderTransactionId(string $transactionId): ?Payment
    {
        return $this->paymentModel->where('provider_transaction_id', $transactionId)->first();
    }

    public function findManyByUserId(string $userId)
    {
        return $this->paymentModel->select(['order_id','ip_address','user_agent','customer_email','customer_name','status','total_amount','currency','created_at','lock_at'])->where('user_id', $userId)->orderBy('created_at','desc')->get();
    }

    public function updateStatus(Payment $payment,$status): bool
    {
        $payment->status = $status;
        return $payment->save();
    }

    public function lockAt($payment): bool
    {
        $payment->lock_at = now();
        return $payment->save();
    }

    public function webhookResponse(Payment $payment,$data): bool
    {
        $payment->webhook_response = json_encode($data);
        return $payment->save();
    }

    public function delete($id)
    {
        return $this->paymentModel->where('id', $id)->delete();
    }

}
