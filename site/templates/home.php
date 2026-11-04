<?php snippet('layout', slots: true) ?>
  <h1 class="visually-hidden"><?= $site->title()->esc() ?></h1>
  <div class="emblem">
    <p class="emblem-loading" role="status">Wait...</p>
    <img class="emblem-poster" src="<?= esc(url('assets/models/emblem.webp'), 'attr') ?>" width="702" height="960" alt="Hyperkavkaz shield emblem">
    <canvas class="emblem-canvas" data-src="<?= esc(url('assets/models/emblem.glb'), 'attr') ?>" aria-hidden="true"></canvas>
    <script data-src="<?= esc(url('assets/js/emblem.js'), 'attr') ?>">
      {
        const loader = document.currentScript;
        const emblem = loader.parentElement;
        emblem.classList.add('is-loading');
        import(loader.dataset.src)
          .catch((error) => console.warn('Unable to render the 3D emblem.', error))
          .finally(() => emblem.classList.remove('is-loading'));
      }
    </script>
  </div>
<?php endsnippet() ?>
