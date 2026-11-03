<?php

use Kirby\Cms\Page;

class ProductPage extends Page
{
  // Keep in sync with the `after` label of the price field in blueprints/pages/product.yml
  public const CURRENCY = 'EUR';

  // Keep in sync with the options of the availability field in blueprints/pages/product.yml
  public const AVAILABILITY = [
    'available' => 'In stock',
    'preorder'  => 'Pre-order',
    'soldout'   => 'Sold out',
  ];

  public function formattedPrice(): ?string
  {
    if ($this->price()->isEmpty()) {
      return null;
    }

    $formatter = new NumberFormatter('en', NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($this->price()->toFloat(), self::CURRENCY);
  }

  public function availabilityLabel(): string
  {
    return self::AVAILABILITY[(string)$this->availability()->value()] ?? self::AVAILABILITY['available'];
  }

  public function isSoldOut(): bool
  {
    return $this->availability()->value() === 'soldout';
  }

  /**
   * The tracklist's lengths added up, as 41:18 (or 1:02:05), if every track has one.
   */
  public function runningTime(): ?string
  {
    $seconds = 0;

    foreach ($tracks = $this->tracklist()->toStructure() as $track) {
      if (preg_match('/^(?:(\d+):)?(\d+):(\d{2})$/', trim($track->length()->value()), $parts) !== 1) {
        return null;
      }

      $seconds += (int)$parts[1] * 3600 + (int)$parts[2] * 60 + (int)$parts[3];
    }

    if ($tracks->isEmpty()) {
      return null;
    }

    $time = sprintf('%d:%02d', intdiv($seconds % 3600, 60), $seconds % 60);

    return $seconds >= 3600 ? intdiv($seconds, 3600) . ':' . str_pad($time, 5, '0', STR_PAD_LEFT) : $time;
  }

  /**
   * The shop link's host, as hyperkavkaz.bandcamp.com, to say where "Buy" leads.
   */
  public function linkHost(): ?string
  {
    $host = parse_url($this->link()->value(), PHP_URL_HOST);

    return is_string($host) ? preg_replace('/^www\./', '', $host) : null;
  }
}
