<?php

use Kirby\Cms\Page;

class PostPage extends Page
{
  // Keep in sync with the options of the category field in blueprints/pages/post.yml
  public const CATEGORIES = [
    'media'  => 'Media',
    'events' => 'Events',
  ];

  public function categoryLabel(): ?string
  {
    return self::CATEGORIES[(string)$this->category()->value()] ?? null;
  }
}
