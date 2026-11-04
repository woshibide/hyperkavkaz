<!doctype html>
<html lang="en">
<head>
  <?php $stylesheet = asset('assets/css/main.css') ?>
  <?= htmlhead()
    ->recommended_minimum()
    ->title(($page->isHomePage() ? '' : $page->title()->esc() . ' | ') . $site->title()->esc())
    ->link_css([$stylesheet->url() . '?v=' . hash_file('xxh3', $stylesheet->root())])
    ->meta_robots(option('hyperkavkaz.preview') ? 'noindex, nofollow' : null)
    ->link_canonical(option('hyperkavkaz.preview') && $page->isErrorPage() ? [] : [$page->url()])
  ?>
  <?php snippet('three-import-map') ?>
  <?= js('assets/js/main.js', ['type' => 'module']) ?>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  <header class="site-header">
    <a class="brand" href="<?= $site->url() ?>" translate="no"<?= $page->isHomePage() ? ' aria-current="page"' : '' ?>>
      <span class="visually-hidden"><?= $site->title()->esc() ?></span>
      <?= svg('assets/svg/hyperkavkaz.svg') ?>
    </a>
    <?php if (($items = $site->children()->listed())->isNotEmpty()): ?>
      <nav class="site-nav" aria-label="Main">
        <ul>
          <?php foreach ($items as $item): ?>
            <li><a href="<?= $item->url() ?>"<?= $item->isActive() ? ' aria-current="page"' : ($item->isOpen() ? ' aria-current="true"' : '') ?>><?= $item->title()->esc() ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>
    <?php endif ?>
  </header>

  <main id="main">
    <?= $slot ?>
  </main>

  <footer class="site-footer">
    <?php if ($page->intendedTemplate()->name() !== 'newsletter'): ?>
      <?php snippet('newsletter') ?>
    <?php endif ?>

    <?php
    // Filled in the Panel, Site > Settings. Empty ones are left out.
    $socials = array_filter([
      'Bandcamp'  => $site->bandcamp(),
      'Instagram' => $site->instagram(),
      'YouTube'   => $site->youtube(),
    ], fn ($url) => $url->isNotEmpty());
    ?>
    <?php if ($socials !== []): ?>
      <ul class="socials" aria-label="Socials">
        <?php foreach ($socials as $label => $url): ?>
          <li><a href="<?= $url->esc('attr') ?>" rel="me"><?= $label ?></a></li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>

    <p class="legal">
      <?php foreach (pages('links', 'privacy') as $item): ?>
        <a href="<?= $item->url() ?>"<?= $item->isActive() ? ' aria-current="page"' : '' ?>><?= $item->title()->esc() ?></a>
      <?php endforeach ?>
      <span class="legal-mark" translate="no">Hyperkavkaz</span>
    </p>
  </footer>

  <?php if (!option('hyperkavkaz.preview')): ?>
    <?php snippet('matomo') ?>
  <?php endif ?>
</body>
</html>
