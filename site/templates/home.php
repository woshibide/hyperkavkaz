<?php snippet('layout', slots: true) ?>
  <h1 class="visually-hidden"><?= $site->title()->esc() ?></h1>
  <div class="emblem">
    <img class="emblem-poster" src="<?= esc(url('assets/models/emblem.webp'), 'attr') ?>" width="702" height="960" alt="Hyperkavkaz shield emblem">
    <canvas class="emblem-canvas" data-src="<?= esc(url('assets/models/emblem.glb'), 'attr') ?>" aria-hidden="true"></canvas>
  </div>
  <?= js('assets/js/emblem.js', ['type' => 'module']) ?>
<?php endsnippet() ?>
