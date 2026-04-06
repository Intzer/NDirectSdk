<?php

return [
    // Таймзона
    'timezone' => 'Europe/Minsk',

    // Не менять!
    'version' => '06.04.2026',

    // Режим отладки (1 - да, 0 - нет)
    'debug' => false,

    // Интеграция, которую вы будете использовать
    'integration' => 'svv',

    // Настройка API
    'api' => [
        // API URL
        'endpoint' => 'https://nextcs.домен/api/v1/',

        // API токен из админки nDirect
        'token' => '',

        // Файл кэша, генерируйте случайный длинный адрес, чтобы нельзя было подобрать
        'cache_file' => __DIR__ . '/db_.sqlite',

        // Время жизни кэша в секундах
        'cache_alive_time' => 120,
    ],

    // Настройки интеграций
    'integrations' => [
        
        'svv' => [
            // random - случайным образом, 
            // connects - по коннектам
            'list_search_sort' => 'random',

            // Включать ли дублирование сервера в МС, если у него разные услуги (true / false)
            'list_search_duplicates' => true,

            // Если у вас есть GS клиент и вы хотите учитывать его коннекты для list_search_sort = 'connects', укажите true, в противном случае - false
            'list_search_count_gs_in_connects_sort' => false,
        ],

    ],
];