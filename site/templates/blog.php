<?php snippet('layout', slots: true) ?>
  <h1><?= $page->title()->esc() ?></h1>
  <nav aria-label="Categories">
    <ul>
      <li><a href="<?= $page->url() ?>"<?= $category ? '' : ' aria-current="page"' ?>>All</a></li>
      <?php foreach (PostPage::CATEGORIES as $value => $label): ?>
        <li><a href="<?= $page->url(['params' => ['category' => $value]]) ?>"<?= $category === $value ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
      <?php endforeach ?>
    </ul>
  </nav>
  <?php if ($posts->isEmpty()): ?>
    <p>No posts yet.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($posts as $post): ?>
        <li data-category="<?= $post->category()->esc('attr') ?>">
          <a href="<?= $post->url() ?>"><?= $post->title()->esc() ?></a>
          <?= esc($post->categoryLabel() ?? '') ?>
          <?php snippet('date', ['date' => $post->date()]) ?>
        </li>
      <?php endforeach ?>
    </ul>
    <?php snippet('pagination', ['pagination' => $pagination]) ?>
  <?php endif ?>
<?php endsnippet() ?>
