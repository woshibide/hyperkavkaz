<?php

use Kirby\Cms\Page;
use Kirby\Cms\Pages;

class ArtistPage extends Page
{
  /**
   * Listed shop products that name this artist, newest release first.
   */
  public function releases(): Pages
  {
    $shop = $this->site()->find('shop');

    if ($shop === null) {
      return new Pages();
    }

    return $shop->children()->listed()
      ->filter(fn ($product) => $product->artist()->toPage()?->is($this) === true)
      ->sortBy('release', 'desc');
  }
}
