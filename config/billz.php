<?php

return [
    'base_url' => env('BILLZ_BASE_URL', 'https://api-admin.billz.ai'),
    'secret_token' => env('BILLZ_SECRET_TOKEN'),
    'rate_limit_ms' => (int) env('BILLZ_RATE_LIMIT_MS', 550),
    'default_warranty_months' => (int) env('BILLZ_DEFAULT_WARRANTY_MONTHS', 12),
];
