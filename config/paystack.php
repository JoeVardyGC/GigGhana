<?php
define('PAYSTACK_PUBLIC_KEY', 'pk_test_YOUR_PUBLIC_KEY_HERE');
define('PAYSTACK_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY_HERE');
define('PAYSTACK_BASE_URL', 'https://api.paystack.co');

function paystackRequest(string $endpoint, array $data = [], string $method = 'POST'): array {
    $url = PAYSTACK_BASE_URL . $endpoint;
    $ch  = curl_init();
    $opts = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = json_encode($data);
    } else {
        $opts[CURLOPT_CUSTOMREQUEST] = 'GET';
    }
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);
    if ($err) return ['status' => false, 'message' => 'cURL Error: ' . $err];
    return json_decode($response, true) ?? ['status' => false, 'message' => 'Invalid response'];
}

function initializePayment(string $email, float $amount, string $reference, array $meta = []): array {
    return paystackRequest('/transaction/initialize', [
        'email'        => $email,
        'amount'       => (int)($amount * 100),
        'reference'    => $reference,
        'metadata'     => $meta,
        'callback_url' => APP_URL . '/api/payments.php?action=verify&ref=' . $reference
    ]);
}

function verifyPayment(string $reference): array {
    return paystackRequest('/transaction/verify/' . urlencode($reference), [], 'GET');
}

function generatePaymentRef(): string {
    return 'GG-' . strtoupper(substr(md5(uniqid('', true)), 0, 12));
}