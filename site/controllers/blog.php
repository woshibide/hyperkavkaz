<?php

use Kirby\Exception\NotFoundException;

return function ($page) {
  $category = param('category');
  $number = param('page');

  if ($category !== null || $number !== null) {
    if (($category !== null && !array_key_exists($category, PostPage::CATEGORIES)) ||
        ($number !== null && !preg_match('/^[1-9][0-9]*$/D', $number))) {
      throw new NotFoundException('Unknown blog filter or page');
    }

    $query = array_filter(['category' => $category, 'page' => $number]);
    go($page->url(['query' => $query]), 301);
  }

  return ['posts' => $page->children()->listed()->sortBy('date', 'desc')];
};
