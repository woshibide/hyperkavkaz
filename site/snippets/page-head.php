<?php
/**
 * The page title, giant and in the script; assets/js/page-title.js fits it to the column.
 * Every template starts with it, unless a page gets a design of its own.
 * Content passed in the default slot follows below the title, like the blog's category tabs:
 * snippet('page-head', slots: true) ... endsnippet()
 *
 * @var Kirby\Cms\Page $page
 */
?>
<header class="page-head">
  <h1 class="page-title"><?= $page->title()->esc() ?></h1>
  <?= $slot ?? '' ?>
</header>
