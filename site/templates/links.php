<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <?php if (($links = $page->links()->toStructure())->isEmpty()): ?>
    <p>No links yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($links as $item): ?>
        <li><a href="<?= $item->link()->esc('attr') ?>"><?= $item->label()->esc() ?></a></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
<?php endsnippet() ?>
