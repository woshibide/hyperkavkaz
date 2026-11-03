<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head', slots: true) ?>
      <nav class="tabs" aria-label="Categories">
        <ul>
          <li><a href="<?= $page->url() ?>"<?= $category ? '' : ' aria-current="page"' ?>>All</a></li>
          <?php foreach (PostPage::CATEGORIES as $value => $label): ?>
            <li><a href="<?= $page->url(['params' => ['category' => $value]]) ?>"<?= $category === $value ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
          <?php endforeach ?>
        </ul>
      </nav>
    <?php endsnippet() ?>

    <?php if ($posts->isEmpty()): ?>
      <p class="empty"><?= $category ? 'No posts in ' . esc(PostPage::CATEGORIES[$category]) . ' yet.' : 'No posts yet.' ?></p>
    <?php else: ?>
      <ul class="post-list">
        <?php foreach ($posts as $post): ?>
          <li class="post-item">
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
      <?php snippet('pagination', ['pagination' => $pagination]) ?>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
