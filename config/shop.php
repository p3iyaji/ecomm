<?php

$adminEmailsRaw = env('SHOP_ADMIN_EMAILS');
if ($adminEmailsRaw === null || trim((string) $adminEmailsRaw) === '') {
    $adminEmailsRaw = in_array(env('APP_ENV'), ['local', 'testing'], true) ? 'admin@example.com' : '';
}

return [
    'currency' => env('SHOP_CURRENCY', 'usd'),
    'tax_rate' => (float) env('SHOP_TAX_RATE', 0.1),
    'admin_emails' => array_filter(array_map('trim', explode(',', (string) $adminEmailsRaw))),
];
