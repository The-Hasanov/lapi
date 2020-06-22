<?php

return [
    'formatters'   => [
        \Lapi\Response\Formatter\DebugFormatter::class
    ],
    'json_options' => JSON_PRETTY_PRINT,
    'debug'        => [
        'request'  => true,
        'route'    => true,
        'duration' => true,
        'query'    => false,
    ]
];
