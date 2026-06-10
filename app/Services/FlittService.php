<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlittService
{
    private string $merchantId;
    private string $secretKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->merchantId = (string) config('services.flitt.merchant_id');
        $this->secretKey  = (string) config('services.flitt.secret_key');
        $this->apiUrl     = rtrim((string) config('services.flitt.api_url', 'https://pay.flitt.io'), '/');
    }

    /**
     * Request a checkout token from Flitt.
     * Throws on any API or network failure.
     *
     * @return array{token: string, payment_id: int|string}
     */
    public function getCheckoutToken(array $params): array
    {
        try {
            $requestParams = array_merge($params, ['merchant_id' => $this->merchantId]);
            $requestParams['signature'] = $this->sign($requestParams);

            $response = Http::timeout(15)->post("{$this->apiUrl}/api/checkout/url", [
                'request' => $requestParams,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException("Flitt HTTP {$response->status()}: {$response->body()}");
            }

            $data = $response->json('response', []);

            if (($data['response_status'] ?? '') !== 'success') {
                throw new \RuntimeException('Flitt error: ' . ($data['error_message'] ?? 'Unknown'));
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('FlittService::getCheckoutToken failed', [
                'error'    => $e->getMessage(),
                'order_id' => $params['order_id'] ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * Verify the signature on a Flitt server callback.
     */
    public function verifyCallback(array $payload): bool
    {
        $received = $payload['signature'] ?? '';
        $toSign   = $payload;
        unset($toSign['signature'], $toSign['response_signature_string']);

        return hash_equals($received, $this->sign($toSign));
    }

    /**
     * Compute Flitt signature: SHA1(secret_key|val1|val2|...) sorted alphabetically by key,
     * empty values excluded.
     */
    private function sign(array $params): string
    {
        $values = collect($params)
            ->filter(fn($v) => $v !== '' && $v !== null)
            ->sortKeys()
            ->values()
            ->prepend($this->secretKey)
            ->map(fn($v) => (string) $v)
            ->toArray();

        return sha1(implode('|', $values));
    }
}
