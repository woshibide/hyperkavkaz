<?php snippet('layout', slots: true) ?>
  <article>
    <h1><?= $page->title()->esc() ?></h1>
    <?php if ($label = $page->categoryLabel()): ?>
      <a href="<?= $page->parent()->url(['params' => ['category' => $page->category()->value()]]) ?>"><?= esc($label) ?></a>
    <?php endif ?>
    <?php snippet('date', ['date' => $page->date()]) ?>
    <?php snippet('cover') ?>
    <?= $page->text()->tiptapText() ?>
  </article>
<?php endsnippet() ?>
