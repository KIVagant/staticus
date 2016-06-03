<?php
require __DIR__ . '/../vendor/autoload.php'; // For the vendor sub-folders tests
require __DIR__ . '/../config/bootstrap.php';
$dotenv = new Dotenv\Dotenv('./');
$dotenv->load();
