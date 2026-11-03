<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>
    <?= $page->text()->tiptapText() ?>
  </div>
<?php endsnippet() ?>
