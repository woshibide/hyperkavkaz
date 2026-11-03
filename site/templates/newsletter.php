<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>
    <div class="newsletter-panel">
      <?php if ($page->text()->isNotEmpty()): ?>
        <div class="newsletter-panel-text"><?= $page->text()->tiptapText() ?></div>
      <?php endif ?>
      <?php snippet('newsletter', ['email' => $email, 'error' => $error, 'done' => $done, 'heading' => false]) ?>
    </div>
  </div>
<?php endsnippet() ?>
