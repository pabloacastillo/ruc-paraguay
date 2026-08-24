<?php

return [
    'base_url' => env('RUC_PARAGUAY_BASE_URL', 'https://www.dnit.gov.py/documents/20123/976078/'),
    'cache_hours' => (int) env('RUC_PARAGUAY_CACHE_HOURS', 48),
    'batch_size' => (int) env('RUC_PARAGUAY_BATCH_SIZE', 2000),
    'search_limit' => (int) env('RUC_PARAGUAY_SEARCH_LIMIT', 50),
];
