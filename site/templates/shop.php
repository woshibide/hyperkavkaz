<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>
    <?php if ($products->isEmpty()): ?>
      <p class="empty">No products yet.</p>
    <?php else: ?>
      <ul class="product-grid">
        <?php foreach ($products as $product): ?>
          <li class="product-card">
            <?php if ($cover = $product->cover()->toFile()): ?>
              <?php $thumb = $cover->crop(600, 600) ?>
              <img src="<?= $thumb->url() ?>" width="<?= $thumb->width() ?>" height="<?= $thumb->height() ?>" alt="" loading="lazy">
            <?php else: ?>
              <div class="product-card-blank" aria-hidden="true"></div>
            <?php endif ?>
            <h2 class="product-card-title"><a href="<?= $product->url() ?>"><?= $product->title()->esc() ?></a></h2>
            <?php if ($artist = $product->artist()->toPage()): ?>
              <p class="product-card-artist"><?= $artist->title()->esc() ?></p>
            <?php endif ?>
            <?php if ($product->format()->isNotEmpty()): ?>
              <p class="product-card-format"><?= $product->format()->esc() ?></p>
            <?php endif ?>
            <p class="product-card-price">
              <?php if ($price = $product->formattedPrice()): ?>
                <span><?= esc($price) ?></span>
              <?php endif ?>
              <?php if ($product->availability()->value() !== 'available'): ?>
                <span class="tag<?= $product->isSoldOut() ? '' : ' tag-strong' ?>"><?= esc($product->availabilityLabel()) ?></span>
              <?php endif ?>
            </p>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
