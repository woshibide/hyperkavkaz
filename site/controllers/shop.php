<?php

return function ($page) {
  // Manual order, as sorted in the Panel
  return [
    'products' => $page->children()->listed(),
  ];
};
