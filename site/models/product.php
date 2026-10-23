<?php

use Kirby\Cms\Page;

class ProductPage extends Page
{
  // Keep in sync with the `after` label of the price field in blueprints/pages/product.yml
  public const CURRENCY = 'EUR';

  public function formattedPrice(): ?string
  {
    if ($this->price()->isEmpty()) {
      return null;
    }

    $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($this->price()->toFloat(), self::CURRENCY);
  }
}
