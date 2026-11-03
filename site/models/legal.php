<?php

use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

class LegalPage extends Page
{
  /**
   * The text's level 2 headings, as [id => title], for the contents list.
   * The ids match the ones site/snippets/tiptap/heading.php gives the headings.
   */
  public function articles(): array
  {
    $doc = json_decode((string)$this->text()->value(), true);
    $articles = [];

    foreach ($doc['content'] ?? [] as $node) {
      if (($node['type'] ?? null) !== 'heading' || ($node['attrs']['level'] ?? null) !== 2) {
        continue;
      }

      $title = implode('', array_map(fn ($child) => $child['text'] ?? '', $node['content'] ?? []));
      $articles[Str::slug($title)] = $title;
    }

    return $articles;
  }
}
