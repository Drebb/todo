<?php
// Database configuration
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: '5432',
    'dbname' => getenv('DB_NAME') ?: 'todos',
    'user' => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: 'postgres'
];
