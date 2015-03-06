<?php
putenv('HTTP_HOST=vm');
putenv('application_id=demo');
putenv('logging=0');


error_reporting(E_ALL);
define('DS', DIRECTORY_SEPARATOR);
define('DIR', __DIR__ . DS);

require_once './vendor/autoload.php';

$dir = DIR . Clanstats\Config\Config::get('cache_dir');
if (file_exists($dir) && is_dir($dir))
	foreach (scandir($dir) as $file) {
		if (is_file($dir . $file))
			unlink($dir . $file);
	}

\Clanstats\Installer::silentInstall();
\Clanstats\Utils\Api::create()->setAppid('demo');
$clan = new \Clanstats\Model\Clans;
$clan->clan_id = 55739;
$clan->save();
