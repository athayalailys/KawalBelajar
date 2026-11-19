<?php

return [
    /* Official baseline rates used only when a master tariff row has not been seeded yet. */
    'default_tariffs' => [
        'SD' => ['online_zoom' => 45000, 'home_visit' => 60000],
        'SMP' => ['online_zoom' => 60000, 'home_visit' => 75000],
        'SMA' => ['online_zoom' => 75000, 'home_visit' => 95000],
        'UMUM' => ['online_zoom' => 85000, 'home_visit' => 110000],
    ],
];
