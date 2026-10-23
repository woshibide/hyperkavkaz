<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <?php if ($products->isEmpty()): ?>
    <p>No products yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($products as $product): ?>
        <li>
          <a href="<?= $product->url() ?>"><?= $product->title()->esc() ?></a>
          <?= esc($product->formattedPrice() ?? '') ?>
        </li>
      <?php endforeach ?>
    </ul>
  <?php endif ?>
<?php endsnippet() ?>
