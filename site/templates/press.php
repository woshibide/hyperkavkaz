<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <?= $page->text()->tiptapText() ?>

  <?php if ($page->email()->isNotEmpty()): ?>
    <section aria-labelledby="press-contact">
      <h2 id="press-contact">Contact</h2>
      <p><?= Html::email($page->email()->value()) ?></p>
    </section>
  <?php endif ?>

  <?php if (($coverage = $page->coverage()->toStructure()->sortBy('date', 'desc'))->isNotEmpty()): ?>
    <section aria-labelledby="press-coverage">
      <h2 id="press-coverage">Coverage</h2>
      <ul>
        <?php foreach ($coverage as $item): ?>
          <li>
            <?= $item->outlet()->esc() ?>:
            <?php if ($item->link()->isNotEmpty()): ?>
              <a href="<?= $item->link()->esc('attr') ?>"><?= $item->headline()->esc() ?></a>
            <?php else: ?>
              <?= $item->headline()->esc() ?>
            <?php endif ?>
            <?php snippet('date', ['date' => $item->date()]) ?>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
  <?php endif ?>

  <?php if (($downloads = $page->files()->sortBy('sort', 'filename'))->isNotEmpty()): ?>
    <section aria-labelledby="press-kit">
      <h2 id="press-kit">Press kit</h2>
      <ul>
        <?php foreach ($downloads as $file): ?>
          <li><a href="<?= $file->url() ?>" download><?= esc($file->filename()) ?></a> (<?= strtoupper($file->extension()) ?>, <?= $file->niceSize() ?>)</li>
        <?php endforeach ?>
      </ul>
    </section>
  <?php endif ?>
<?php endsnippet() ?>
