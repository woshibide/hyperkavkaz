<?php

use Kirby\Exception\NotFoundException;

return function ($page) {
  $posts = $page->children()->listed()->sortBy('date', 'desc');

  // Optional filter: /blog/category:events
  if ($category = param('category')) {
    if (array_key_exists($category, PostPage::CATEGORIES) === false) {
      throw new NotFoundException('Unknown category');
    }

    $posts = $posts->filterBy('category', $category);
  }

  $posts = $posts->paginate(10);

  return [
    'posts'      => $posts,
    'category'   => $category,
    'pagination' => $posts->pagination(),
  ];
};
