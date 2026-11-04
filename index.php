<?php

require 'kirby/bootstrap.php';

$kirby = new Kirby;

if (getenv('HYPERKAVKAZ_HOLDING_PAGE') === '1') {
  if ($kirby->request()->path()->isEmpty() === false) {
    http_response_code(404);
    return;
  }

  snippet('holding-page');
  return;
}

echo $kirby->render();
