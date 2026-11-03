<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>

    <?php if ($page->text()->isNotEmpty()): ?>
      <div class="lead-text"><?= $page->text()->tiptapText() ?></div>
    <?php endif ?>

    <?php if ($page->email()->isNotEmpty()): ?>
      <section class="contact-card" aria-labelledby="press-contact">
        <h2 id="press-contact">Press contact</h2>
        <p class="contact-card-email"><?= Html::email($page->email()->value()) ?></p>
      </section>
    <?php endif ?>

    <?php if (($coverage = $page->coverage()->toStructure()->sortBy('date', 'desc'))->isNotEmpty()): ?>
      <section class="section" aria-labelledby="press-coverage">
        <h2 class="section-title" id="press-coverage">Coverage</h2>
        <table class="coverage">
          <thead>
            <tr>
              <th scope="col">Date</th>
              <th scope="col">Outlet</th>
              <th scope="col">Article</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($coverage as $item): ?>
              <tr>
                <td class="coverage-date"><?php snippet('date', ['date' => $item->date()]) ?></td>
                <td class="coverage-outlet"><?= $item->outlet()->esc() ?></td>
                <td class="coverage-headline">
                  <?php if ($item->link()->isNotEmpty()): ?>
                    <a href="<?= $item->link()->esc('attr') ?>"><?= $item->headline()->esc() ?></a>
                  <?php else: ?>
                    <?= $item->headline()->esc() ?>
                  <?php endif ?>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </section>
    <?php endif ?>

    <?php if (($downloads = $page->files()->sortBy('sort', 'filename'))->isNotEmpty()): ?>
      <section class="section" aria-labelledby="press-kit">
        <h2 class="section-title" id="press-kit">Press kit</h2>
        <ul class="downloads">
          <?php foreach ($downloads as $file): ?>
            <li>
              <a href="<?= $file->url() ?>" download>
                <span class="downloads-type" aria-hidden="true"><?= Str::upper($file->extension()) ?></span>
                <span class="downloads-name"><?= esc($file->filename()) ?></span>
                <span class="downloads-size"><?= Str::upper($file->extension()) ?>, <?= $file->niceSize() ?></span>
              </a>
            </li>
          <?php endforeach ?>
        </ul>
      </section>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
