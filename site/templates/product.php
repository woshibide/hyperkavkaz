<?php snippet('layout', slots: true) ?>
  <article>
    <h1><?= $page->title()->esc() ?></h1>
    <?php if ($artist = $page->artist()->toPage()): ?>
      <p><a href="<?= $artist->url() ?>"><?= $artist->title()->esc() ?></a></p>
    <?php endif ?>
    <?php if ($price = $page->formattedPrice()): ?>
      <p><?= esc($price) ?></p>
    <?php endif ?>
    <?php snippet('cover') ?>
    <?= $page->text()->tiptapText() ?>
  </article>
<?php endsnippet() ?>
