<?php snippet('layout', slots: true) ?>
  <article>
    <h1><?= $page->title()->esc() ?></h1>
    <?php snippet('cover') ?>
    <?= $page->text()->tiptapText() ?>
  </article>
<?php endsnippet() ?>
