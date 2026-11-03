<?php
$artist = $page->artist()->toPage();
$details = array_filter([
  'Artist'           => $artist ? '<a href="' . $artist->url() . '">' . $artist->title()->esc() . '</a>' : '',
  'Format'           => (string)$page->format()->esc(),
  'Catalogue number' => (string)$page->catalogue()->esc(),
]);
?>
<?php snippet('layout', slots: true) ?>
  <article class="column">
    <?php snippet('page-head', slots: true) ?>
      <?php if ($artist): ?>
        <p class="page-head-note"><a href="<?= $artist->url() ?>"><?= $artist->title()->esc() ?></a></p>
      <?php endif ?>
    <?php endsnippet() ?>

    <div class="product-stage">
      <?php if ($page->cover()->toFile()): ?>
        <div class="product-cover"><?php snippet('cover') ?></div>
      <?php endif ?>

      <div class="purchase">
        <?php if ($price = $page->formattedPrice()): ?>
          <p class="purchase-price"><?= esc($price) ?></p>
        <?php endif ?>
        <p><span class="tag<?= $page->availability()->value() === 'preorder' ? ' tag-strong' : '' ?>"><?= esc($page->availabilityLabel()) ?></span></p>
        <?php if ($page->isSoldOut()): ?>
          <p class="purchase-note">Represses and new releases are announced in the <a href="#newsletter">newsletter</a>.</p>
        <?php elseif ($page->link()->isNotEmpty()): ?>
          <a class="button" href="<?= $page->link()->esc('attr') ?>"><?= $page->availability()->value() === 'preorder' ? 'Pre-order' : 'Buy' ?></a>
          <p class="purchase-note">
            Opens <?= esc($page->linkHost() ?? 'the shop') ?>, where the order is placed.
            <?php if ($page->availability()->value() === 'preorder' && $page->release()->isNotEmpty()): ?>
              Ships on release, <?php snippet('date', ['date' => $page->release()]) ?>.
            <?php endif ?>
          </p>
        <?php endif ?>
      </div>
    </div>

    <?php if ($page->text()->isNotEmpty()): ?>
      <div class="prose section"><?= $page->text()->tiptapText() ?></div>
    <?php endif ?>

    <?php if ($details !== [] || $page->release()->isNotEmpty()): ?>
      <section class="section" aria-labelledby="details">
        <h2 class="section-title" id="details">Details</h2>
        <dl class="facts">
          <?php foreach ($details as $label => $value): ?>
            <div><dt><?= $label ?></dt><dd><?= $value ?></dd></div>
          <?php endforeach ?>
          <?php if ($page->release()->isNotEmpty()): ?>
            <div><dt>Release date</dt><dd><?php snippet('date', ['date' => $page->release()]) ?></dd></div>
          <?php endif ?>
        </dl>
      </section>
    <?php endif ?>

    <?php if (($tracks = $page->tracklist()->toStructure())->isNotEmpty()): ?>
      <section class="section" aria-labelledby="tracklist">
        <h2 class="section-title" id="tracklist">Tracklist</h2>
        <ol class="tracklist">
          <?php foreach ($tracks as $track): ?>
            <li>
              <span class="tracklist-title"><?= $track->title()->esc() ?></span>
              <?php if ($track->length()->isNotEmpty()): ?>
                <span class="tracklist-length"><?= $track->length()->esc() ?></span>
              <?php endif ?>
            </li>
          <?php endforeach ?>
        </ol>
        <?php if ($total = $page->runningTime()): ?>
          <p class="tracklist-total">Total <?= $total ?></p>
        <?php endif ?>
      </section>
    <?php endif ?>

    <p class="back-link"><a href="<?= $page->parent()->url() ?>">All products</a></p>
  </article>
<?php endsnippet() ?>
