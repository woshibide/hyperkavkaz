<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>
    <p class="lead">This address leads nowhere. It may be mistyped, or the page has moved. One of these sections should have it:</p>
    <ul class="sitemap">
      <li><a href="<?= $site->url() ?>">Home</a></li>
      <?php foreach ($site->children()->listed() as $item): ?>
        <li><a href="<?= $item->url() ?>"><?= $item->title()->esc() ?></a></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endsnippet() ?>
