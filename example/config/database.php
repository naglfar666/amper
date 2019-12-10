<?php
return [
    'connection' => [
        'prefix' => 'amper_', // prefix for table
        'user' => ENV('DB_USER') ? ENV('DB_USER') : 'root', // db user
        'password' => ENV('DB_PASSWORD') ? ENV('DB_PASSWORD') : '', // db password
        'driver' => 'mysql', // db access driver
        'host' => 'localhost', // db host
        'name' => 'db_name' // db name
    ],
    'redis' => [ // array directly passing to Predis\Client
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ],
    'entities' => [ // All registered entities
        'ExampleEntity'
    ]

];

?>
