<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The developer API (api/v1/*) is designed to be called from other
    | domains (e.g. a partner site embedding availability/booking), and is
    | authenticated with a bearer token rather than cookies, so a permissive
    | origin policy is safe here. supports_credentials stays false since no
    | cookie/session state is ever read on these routes.
    |
    */

    'paths' => ['api/v1/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
