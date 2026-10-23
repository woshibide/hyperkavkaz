<?php

// Local development (composer start serves on localhost:8000)
return [
  'debug' => true,

  // No static files locally: the dev server would not serve them anyway
  'cache' => [
    'pages' => [
      'active' => false,
    ],
  ],
];
