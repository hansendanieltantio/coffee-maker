<?php

return [
    'role' => env('ORDER_NODE_ROLE', 'cloud'),
    'id' => env('ORDER_NODE_ID', 'edge-1'),
    'cloud_url' => env('ORDER_CLOUD_URL'),
    'shared_token' => env('ORDER_NODE_TOKEN'),
    'device_token' => env('ORDER_DEVICE_TOKEN'),
    'sync_interval' => (int) env('ORDER_SYNC_INTERVAL', 2),
];
