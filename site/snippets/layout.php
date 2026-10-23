<!doctype html>
<html lang="en">
<head>
  <?= htmlhead()
    ->recommended_minimum()
    ->title(($page->isHomePage() ? '' : $page->title()->esc() . ' | ') . $site->title()->esc())
    ->link_css(['https://use.typekit.net/mrh7ota.css', 'assets/css/main.css'])
    ->meta_robots()
    ->link_canonical([$page->url()])
  ?>
  <?php /* The import map must come before the first module script. */ ?>
  <script type="importmap">
    {
      "imports": {
        "three": "<?= url('assets/js/vendor/three/three.module.js') ?>",
        "three/addons/": "<?= url('assets/js/vendor/three/addons/') ?>"
      }
    }
  </script>
  <?= js('assets/js/main.js', ['type' => 'module']) ?>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  <header>
    <a href="<?= $site->url() ?>" translate="no"<?= $page->isHomePage() ? ' aria-current="page"' : '' ?>><?= $site->title()->esc() ?></a>
    <?php if (($items = $site->children()->listed())->isNotEmpty()): ?>
      <nav aria-label="Main">
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

  <footer>
    <?php if ($page->intendedTemplate()->name() !== 'newsletter'): ?>
      <?php snippet('newsletter') ?>
    <?php endif ?>
    <?php foreach (pages('links', 'privacy') as $item): ?>
      <a href="<?= $item->url() ?>"<?= $item->isActive() ? ' aria-current="page"' : '' ?>><?= $item->title()->esc() ?></a>
    <?php endforeach ?>
  </footer>

  <?php snippet('matomo') ?>
</body>
</html>
