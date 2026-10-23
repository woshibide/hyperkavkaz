<?php snippet('layout', slots: true) ?>
  <h1 class="visually-hidden"><?= $site->title()->esc() ?></h1>
  <canvas class="wordmark" aria-hidden="true" data-src="<?= url('assets/svg/hyperkavkaz.svg') ?>"></canvas>
  <script type="module" src="<?= url('assets/js/wordmark.js') ?>"></script>
<?php endsnippet() ?>
