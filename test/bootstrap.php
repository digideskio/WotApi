<?php


error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('DIR', __DIR__ . DS);

require_once './vendor/autoload.php';


if (file_exists(DIR.'.env')) Dotenv::load(__DIR__);

\WotApi\Api::create()->setApplicationId('demo');
