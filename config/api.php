<?php

return [
    'formatters'   => [
        \Lapi\Response\Formatter\DebugFormatter::class,
        //\Lapi\Response\Formatter\OrderBodyFormatter::class,
    ],
    'json_options' => JSON_PRETTY_PRINT,
    'pagination'   => [
        'default_limit' => 15,
        'max_limit'     => 100,
    ],

    'debug' => [
        'request'  => true,
        'route'    => true,
        'duration' => true,
        'query'    => false,
    ],
    /*
    'order_body'   => [

    ],
    */
];
