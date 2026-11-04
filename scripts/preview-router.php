<?php

// Used by the loopback PHP server during the static export.
if (PHP_SAPI !== 'cli-server' || getenv('HYPERKAVKAZ_PREVIEW') !== '1') {
  http_response_code(404);
  return;
}

$_SERVER['HTTPS'] = 'on';
$_SERVER['SCRIPT_NAME'] = '/hyperkavkaz/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/index.php';

require $_SERVER['SCRIPT_FILENAME'];
