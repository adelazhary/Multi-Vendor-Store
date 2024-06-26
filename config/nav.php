<?php

return [
    [
        'icon' => 'nav-icon fas fa-tachometer-alt',
        'title' => 'Dashboard',
        'route' => 'dashboard',
        'active' => 'dashboard',
    ],
    [
        'icon' => 'nav-icon fas fa-list',
        'title' => 'Categories',
        'route' => 'dashboard.categories.index',
        'badge' => 'new',
        'active' => 'dashboard.categories.*',
        'badgeColor' => 'success'
    ],
    [
        'icon' => 'nav-icon fas fa-list',
        'title' => 'Products',
        'route' => 'products.index',
        'active' => 'products.*',
    ],
    // [
    //     'icon' => 'nav-icon fas fa-list',
    //     'title' => 'Orders',
    //     'route' => 'dashboard.orders.index',
    // ],
    // [
    //     'icon' => 'nav-icon fas fa-list',
    //     'title' => 'Settings',
    //     'route' => 'dashboard.settings.index',
    // ],
];
