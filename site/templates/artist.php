<?php snippet('layout', slots: true) ?>
  <article class="column">
    <?php snippet('page-head', slots: true) ?>
      <?php if ($page->origin()->isNotEmpty()): ?>
        <p class="page-head-note">Based in <?= $page->origin()->esc() ?></p>
      <?php endif ?>
    <?php endsnippet() ?>

    <?php if ($page->cover()->toFile()): ?>
      <div class="portrait"><?php snippet('cover') ?></div>
    <?php endif ?>

    <?php if ($page->text()->isNotEmpty()): ?>
      <section class="section" aria-labelledby="biography">
        <h2 class="section-title" id="biography">Biography</h2>
        <div class="prose"><?= $page->text()->tiptapText() ?></div>
      </section>
    <?php endif ?>

    <?php if (($releases = $page->releases())->isNotEmpty()): ?>
      <section class="section" aria-labelledby="discography">
        <h2 class="section-title" id="discography">Discography</h2>
        <ul class="release-grid">
          <?php foreach ($releases as $release): ?>
            <li class="release">
              <?php if ($cover = $release->cover()->toFile()): ?>
                <?php $thumb = $cover->crop(400, 400) ?>
                <img src="<?= $thumb->url() ?>" width="<?= $thumb->width() ?>" height="<?= $thumb->height() ?>" alt="" loading="lazy">
              <?php endif ?>
              <h3 class="release-title"><a href="<?= $release->url() ?>"><?= $release->title()->esc() ?></a></h3>
              <p class="release-meta">
                <?= implode(', ', array_filter([
                  $release->release()->isNotEmpty() ? $release->release()->toDate('Y') : null,
                  (string)$release->format()->esc(),
                ])) ?>
              </p>
            </li>
          <?php endforeach ?>
        </ul>
      </section>
    <?php endif ?>

    <?php if (($links = $page->links()->toStructure())->isNotEmpty()): ?>
      <section class="section" aria-labelledby="artist-links">
        <h2 class="section-title" id="artist-links">Links</h2>
        <ul class="pill-list">
          <?php foreach ($links as $item): ?>
            <li><a href="<?= $item->link()->esc('attr') ?>"><?= $item->label()->esc() ?></a></li>
          <?php endforeach ?>
        </ul>
      </section>
    <?php endif ?>

    <p class="back-link"><a href="<?= $page->parent()->url() ?>">All artists</a></p>
  </article>
<?php endsnippet() ?>
