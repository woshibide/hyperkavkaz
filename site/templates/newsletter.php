<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <?= $page->text()->tiptapText() ?>
  <?php snippet('newsletter', compact('email', 'error', 'done')) ?>
<?php endsnippet() ?>
