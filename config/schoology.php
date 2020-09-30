<?php

return array(
    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Set some default values. It is possible to add all defines that can be set
    | in dompdf_config.inc.php. You can also override the entire config file.
    |
    */
    'show_warnings' => env('SCHOOLOGY_SHOW_WARNINGS', false),   // Throw an Exception on warnings from dompdf

    'auth' => array (
        'default_mode' => env('SCHOOLOGY_AUTH_MODE', 'two_legged'),

        /**
         * Use Schoology as a specific pre-authorized user.
         */
        'two_legged' => array(
            /**
             * The Schoology domain
             */
            "domain" => env('SCHOOLOGY_TWOLEGGED_DOMAIN'),
        
            /**
             *  Consumer key of the user
             */
            "consumer_key" => env('SCHOOLOGY_TWOLEGGED_CONSUMER_KEY'),
        
            /**
             * Consumer secret of the user
             */
            "consumer_secret" => env('SCHOOLOGY_TWOLEGGED_CONSUMER_SECRET'),
        ),
        
        /**
         * Use Schoology as a published app that requests access to a specific user's account.
         */
        'three_legged' => array(
            /**
             * The Schoology domain
             */
            "domain" => env('SCHOOLOGY_THREELEGGED_DOMAIN'),
        
            /**
             * Consumer key of the app
             */
            "consumer_key" => env('SCHOOLOGY_THREELEGGED_CONSUMER_KEY'),
        
            /**
             * Consumer secret of the app
             */
            "consumer_secret" => env('SCHOOLOGY_THREELEGGED_CONSUMER_SECRET'),
        ),
    )
);
