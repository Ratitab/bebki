<?php

namespace App\DTO;

use Illuminate\Http\Request;

class PaymentDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly ?string $transactionId = null,
        public readonly ?string $orderId = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $customerName = null,
        public readonly ?string $paymentProvider = null,
        public readonly ?string $providerTransactionId = null,
        public readonly string $status = 'PENDING',
        public readonly ?string $statusDescription = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $cancelledAt = null,
        public readonly ?string $refundedAt = null,
        public readonly ?string $notes = null,
        public readonly array $paymentData = [],
        public readonly float $totalAmount = 0,
        public readonly string $currency = 'GEL',
        public readonly float $exchangeRate = 0,
        public readonly ?string $invoiceUrl = null
    ) {}


    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            transactionId: $data['transaction_id'] ?? null,
            orderId: $data['order_id'] ?? null,
            ipAddress: $data['ip_address'] ?? null,
            userAgent: $data['user_agent'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerName: $data['customer_name'] ?? null,
            paymentProvider: $data['payment_provider'] ?? null,
            providerTransactionId: $data['provider_transaction_id'] ?? null,
            status: $data['status'] ?? 'PENDING',
            statusDescription: $data['status_description'] ?? null,
            errorCode: $data['error_code'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            cancelledAt: $data['cancelled_at'] ?? null,
            refundedAt: $data['refunded_at'] ?? null,
            notes: $data['notes'] ?? null,
            paymentData: $data['payment_data'] ?? [],
            totalAmount: $data['total_amount'],
            currency: $data['currency'] ?? 'GEL',
            exchangeRate: $data['exchange_rate'] ?? 0,
            invoiceUrl: $data['invoice_url'] ?? null
        );
    }
    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->input('user_id'),
            transactionId: $request->input('transaction_id'),
            orderId: $request->input('order_id'),
            ipAddress: $request->ip(),
            userAgent: $request->header('User-Agent'),
            customerEmail: $request->input('customer_email'),
            customerName: $request->input('customer_name'),
            paymentProvider: $request->input('payment_provider'),
            providerTransactionId: $request->input('provider_transaction_id'),
            status: $request->input('status', 'PENDING'),
            statusDescription: $request->input('status_description'),
            errorCode: $request->input('error_code'),
            errorMessage: $request->input('error_message'),
            cancelledAt: $request->input('cancelled_at'),
            refundedAt: $request->input('refunded_at'),
            notes: $request->input('notes'),
            paymentData: $request->input('payment_data', []),
            totalAmount: $request->input('total_amount'),
            currency: $request->input('currency', 'GEL'),
            exchangeRate: $request->input('exchange_rate', 0),
            invoiceUrl: $request->input('invoice_url')
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->userId,
            'transaction_id' => $this->transactionId,
            'order_id' => $this->orderId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'customer_email' => $this->customerEmail,
            'customer_name' => $this->customerName,
            'payment_provider' => $this->paymentProvider,
            'provider_transaction_id' => $this->providerTransactionId,
            'status' => $this->status,
            'status_description' => $this->statusDescription,
            'error_code' => $this->errorCode,
            'error_message' => $this->errorMessage,
            'cancelled_at' => $this->cancelledAt,
            'refunded_at' => $this->refundedAt,
            'notes' => $this->notes,
            'payment_data' => $this->paymentData,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchangeRate,
            'invoice_url' => $this->invoiceUrl,
        ], fn($value) => !is_null($value));
    }
}
