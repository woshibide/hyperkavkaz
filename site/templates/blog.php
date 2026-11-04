<?php snippet('layout', slots: true) ?>
  <div class="column" data-blog>
    <?php snippet('page-head', slots: true) ?>
      <nav class="tabs" aria-label="Categories" hidden>
        <ul>
          <li><a href="<?= $page->url() ?>" data-category="">All</a></li>
          <?php foreach (PostPage::CATEGORIES as $value => $label): ?>
            <li><a href="<?= esc($page->url(['query' => ['category' => $value]]), 'attr') ?>" data-category="<?= esc($value, 'attr') ?>"><?= esc($label) ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>
    <?php endsnippet() ?>

    <p class="empty" role="status"<?= $posts->isEmpty() ? '' : ' hidden' ?>>No posts yet.</p>
    <?php if ($posts->isNotEmpty()): ?>
      <ul class="post-list">
        <?php foreach ($posts as $post): ?>
          <li class="post-item" data-post-category="<?= $post->category()->esc('attr') ?>">
            <div class="post-item-text">
              <h2 class="post-item-title"><a href="<?= $post->url() ?>"><?= $post->title()->esc() ?></a></h2>
              <p class="post-item-meta">
                <?php snippet('date', ['date' => $post->date()]) ?>
                <?php if ($label = $post->categoryLabel()): ?>
                  <span class="tag"><?= esc($label) ?></span>
                <?php endif ?>
              </p>
            </div>
            <?php if ($cover = $post->cover()->toFile()): ?>
              <?php $thumb = $cover->crop(320, 240) ?>
              <img src="<?= $thumb->url() ?>" width="<?= $thumb->width() ?>" height="<?= $thumb->height() ?>" alt="" loading="lazy">
            <?php endif ?>
          </li>
        <?php endforeach ?>
      </ul>
      <nav class="pagination" aria-label="Pagination" hidden></nav>
    <?php endif ?>
  </div>
  <?= js('assets/js/blog.js', ['type' => 'module']) ?>
<?php endsnippet() ?>
