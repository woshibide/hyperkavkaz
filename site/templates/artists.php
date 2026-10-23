<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <?php if ($artists->isEmpty()): ?>
    <p>No artists yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($artists as $artist): ?>
        <li><a href="<?= $artist->url() ?>"><?= $artist->title()->esc() ?></a></li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
<?php endsnippet() ?>
