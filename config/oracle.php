<?php

return [
    'oracle' => [
        'driver'        => 'oracle',
        'tns'           => env('ORACLE_DB_TNS', ''),
        'host'          => env('ORACLE_DB_HOST', ''),
        'port'          => env('ORACLE_DB_PORT', '1521'),
        'database'      => env('ORACLE_DB_DATABASE', ''),
        'service_name'  => env('ORACLE_DB_SERVICE_NAME', ''),
        'username'      => env('ORACLE_DB_USERNAME', ''),
        'password'      => env('ORACLE_DB_PASSWORD', ''),
        'charset'       => env('ORACLE_DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('ORACLE_DB_PREFIX', ''),
        'prefix_schema' => env('ORACLE_DB_SCHEMA_PREFIX', ''),
        //'edition'        => env('DB_EDITION', 'ora$base'),
        //'server_version' => env('DB_SERVER_VERSION', '11g'),
        'load_balance'   => env('DB_LOAD_BALANCE', 'yes'),
        //'dynamic'        => [],
    ],

    'oracle-fbnm' => [
        'driver'        => 'oracle',
        'tns'           => env('ORACLE_FBNM_DB_TNS', ''),
        'host'          => env('ORACLE_FBNM_DB_HOST', ''),
        'port'          => env('ORACLE_FBNM_DB_PORT', '1521'),
        'database'      => env('ORACLE_FBNM_DB_DATABASE', ''),
        'service_name'  => env('ORACLE_FBNM_DB_SERVICE_NAME', ''),
        'username'      => env('ORACLE_FBNM_DB_USERNAME', ''),
        'password'      => env('ORACLE_FBNM_DB_PASSWORD', ''),
        'charset'       => env('ORACLE_FBNM_DB_CHARSET', 'AL32UTF8'),
        'prefix'        => env('ORACLE_FBNM_DB_PREFIX', ''),
        'prefix_schema' => env('ORACLE_FBNM_DB_SCHEMA_PREFIX', ''),
        //'edition'        => env('DB_EDITION', 'ora$base'),
        //'server_version' => env('DB_SERVER_VERSION', '11g'),
        'load_balance'   => env('DB_LOAD_BALANCE', 'yes'),
        //'dynamic'        => [],
    ],
];
