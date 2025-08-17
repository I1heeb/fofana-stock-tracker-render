<?php

return [
    'name' => 'Fofana Stock Management',
    'manifest' => [
        'name' => env('APP_NAME', 'Fofana Stock Management'),
        'short_name' => 'Fofana Stock',
        'start_url' => '/dashboard',
        'background_color' => '#ffffff',
        'theme_color' => '#2563eb',
        'display' => 'standalone',
        'orientation' => 'portrait-primary',
        'status_bar' => 'black-translucent',
        'icons' => [
            '72x72' => [
                'path' => '/icons/icon-72x72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/icons/icon-96x96.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/icons/icon-128x128.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/icons/icon-144x144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/icons/icon-152x152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/icons/icon-192x192.png',
                'purpose' => 'any maskable'
            ],
            '384x384' => [
                'path' => '/icons/icon-384x384.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/icons/icon-512x512.png',
                'purpose' => 'any maskable'
            ]
        ],
        'splash' => [
            '640x1136' => '/icons/splash-640x1136.png',
            '750x1334' => '/icons/splash-750x1334.png',
            '828x1792' => '/icons/splash-828x1792.png',
            '1125x2436' => '/icons/splash-1125x2436.png',
            '1242x2208' => '/icons/splash-1242x2208.png',
            '1242x2688' => '/icons/splash-1242x2688.png',
            '1536x2048' => '/icons/splash-1536x2048.png',
            '1668x2224' => '/icons/splash-1668x2224.png',
            '1668x2388' => '/icons/splash-1668x2388.png',
            '2048x2732' => '/icons/splash-2048x2732.png'
        ],
        'shortcuts' => [
            [
                'name' => 'New Order',
                'description' => 'Create a new order quickly',
                'url' => '/orders/create',
                'icons' => [
                    'src' => '/icons/shortcut-order.png',
                    'purpose' => 'any'
                ]
            ],
            [
                'name' => 'Products',
                'description' => 'View product inventory',
                'url' => '/products',
                'icons' => [
                    'src' => '/icons/shortcut-products.png',
                    'purpose' => 'any'
                ]
            ]
        ],
        'custom' => []
    ]
];