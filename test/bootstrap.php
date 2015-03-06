<?php
putenv('HTTP_HOST=vm');
putenv('application_id=demo');


error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('DIR', __DIR__ . DS);

require_once './vendor/autoload.php';

\WotApi\Api::create()->setAppid('demo');
