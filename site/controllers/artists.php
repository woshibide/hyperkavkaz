<?php

return function ($page) {
  return [
    'artists' => $page->children()->listed()->sortBy('title', 'asc'),
  ];
};
