<?php

return [
    // Override the RBAC guard for API‑only consumers.
    'rbac' => [
        'guard' => 'sanctum',
    ],
];
