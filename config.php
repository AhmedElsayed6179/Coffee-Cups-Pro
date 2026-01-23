<?php
$host = 'sql100.infinityfree.com';
$user = 'if0_40222745';
$password = 'yQnuAaW9O4n';
$database = 'if0_40222745_coffeecups';

$connection = new mysqli($host, $user, $password, $database);
$connection->set_charset("utf8mb4");

if ($connection->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]));
}

return $connection;
