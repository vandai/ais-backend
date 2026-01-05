<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Helper to simulate logic
function getRedirectTarget($roles) {
    if (in_array('member', $roles)) {
        return 'dashboard';
    } elseif (in_array('guest', $roles)) {
        return 'guest-dashboard';
    }
    return 'fallback';
}

function checkLoginAllowed($roles) {
    $roles = is_array($roles) ? $roles : [];
    return in_array('member', $roles) || in_array('guest', $roles);
}

// Test Data
$tests = [
    ['role' => ['member'], 'expected_login' => true, 'expected_route' => 'dashboard'],
    ['role' => ['guest'], 'expected_login' => true, 'expected_route' => 'guest-dashboard'],
    ['role' => ['member', 'guest'], 'expected_login' => true, 'expected_route' => 'dashboard'], // Member priority
    ['role' => ['admin'], 'expected_login' => false, 'expected_route' => 'fallback'],
    ['role' => [], 'expected_login' => false, 'expected_route' => 'fallback'],
];

echo "Testing Redirect & Login Logic:\n";

foreach ($tests as $case) {
    $roles = $case['role'];
    $loginAllowed = checkLoginAllowed($roles);
    $target = $loginAllowed ? getRedirectTarget($roles) : 'blocked';
    
    echo "Roles: " . json_encode($roles) . 
         " | Login Allowed: " . ($loginAllowed ? 'YES' : 'NO') . 
         " | Redirect Target: " . $target . "\n";
         
    if ($loginAllowed !== $case['expected_login']) echo "FAIL Login Check\n";
    if ($loginAllowed && $target !== $case['expected_route']) echo "FAIL Redirect Target (Expected {$case['expected_route']})\n";
}
