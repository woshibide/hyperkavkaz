<?php snippet('layout', slots: true) ?>
  <article class="column">
    <?php snippet('page-head', slots: true) ?>
      <p class="page-head-note">
        <?php snippet('date', ['date' => $page->date()]) ?>
        <?php if ($label = $page->categoryLabel()): ?>
          <a class="tag" href="<?= $page->parent()->url(['params' => ['category' => $page->category()->value()]]) ?>"><?= esc($label) ?></a>
        <?php endif ?>
      </p>
    <?php endsnippet() ?>

    <?php if ($page->cover()->toFile()): ?>
      <div class="post-cover"><?php snippet('cover') ?></div>
    <?php endif ?>

    <div class="prose"><?= $page->text()->tiptapText() ?></div>

    <?php
    // Folders are numbered by date, so the previous sibling is the older post.
    $older = $page->prevListed();
    $newer = $page->nextListed();
    ?>
    <nav class="post-pager" aria-label="More posts">
      <?php if ($older): ?>
        <a class="post-pager-older" href="<?= $older->url() ?>" rel="prev">
          <span class="post-pager-label">Older post</span>
          <span class="post-pager-title"><?= $older->title()->esc() ?></span>
        </a>
      <?php endif ?>
      <?php if ($newer): ?>
        <a class="post-pager-newer" href="<?= $newer->url() ?>" rel="next">
          <span class="post-pager-label">Newer post</span>
          <span class="post-pager-title"><?= $newer->title()->esc() ?></span>
        </a>
      <?php endif ?>
    </nav>

    <p class="back-link"><a href="<?= $page->parent()->url() ?>">All posts</a></p>
  </article>
<?php endsnippet() ?>
