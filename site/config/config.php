<?php

/**
 * Production defaults.
 * Host-specific overrides live in config.{host}.php.
 * Secrets come from environment variables (set them in the fortrabbit dashboard).
 * https://getkirby.com/docs/reference/system/options
 */
$preview = getenv('HYPERKAVKAZ_PREVIEW') === '1';

return [
  'hyperkavkaz.preview' => $preview,
  ...($preview ? ['url' => 'https://woshibide.github.io/hyperkavkaz'] : []),
  'debug' => false,

  // (image:) tags get width and height from the file, to prevent layout shift
  'kirbytext' => [
    'image' => [
      'width'  => 'auto',
      'height' => 'auto',
    ],
  ],

  // Staticache: rendered pages are written to site/cache/{host}/pages/
  // and served by Apache (.htaccess) to anonymous GET requests.
  'cache' => [
    'pages' => [
      'active' => !$preview,
      'type'   => 'static',
    ],
  ],

  // Admin bar on the frontend for logged-in Panel users
  'ready' => fn ($kirby) => [
    'pechente.kirby-admin-bar' => [
      'active' => !option('hyperkavkaz.preview') && $kirby->user() !== null,
    ],
  ],

  // Loop (client feedback): switched on and off in the Panel, Site > Settings.
  // Only logged-in Panel users see it.
  'moinframe.loop.enabled' => fn () => site()->content()->get('loop')->toBool(),

  // Matomo, self-hosted and cookieless. Inactive until MATOMO_URL and MATOMO_SITE_ID are set.
  'sylvainjule.matomo.url'            => getenv('MATOMO_URL') ?: false,
  'sylvainjule.matomo.id'             => getenv('MATOMO_SITE_ID') ?: false,
  'sylvainjule.matomo.token'          => getenv('MATOMO_TOKEN') ?: false,
  'sylvainjule.matomo.disableCookies' => true,
  'sylvainjule.matomo.area'           => getenv('MATOMO_TOKEN') !== false,
];
