<?php $articles = $page->articles() ?>
<?php snippet('layout', slots: true) ?>
  <article class="column">
    <?php snippet('page-head', slots: true) ?>
      <?php if ($page->effective()->isNotEmpty() || $page->revised()->isNotEmpty()): ?>
        <p class="page-head-note">
          <?php if ($page->effective()->isNotEmpty()): ?>
            <span>In effect since <?php snippet('date', ['date' => $page->effective()]) ?>.</span>
          <?php endif ?>
          <?php if ($page->revised()->isNotEmpty()): ?>
            <span>Last revised <?php snippet('date', ['date' => $page->revised()]) ?>.</span>
          <?php endif ?>
        </p>
      <?php endif ?>
    <?php endsnippet() ?>

    <?php if (count($articles) > 1): ?>
      <nav class="contents" aria-labelledby="contents-title">
        <h2 id="contents-title">Contents</h2>
        <ol>
          <?php foreach ($articles as $id => $title): ?>
            <li><a href="#<?= $id ?>"><?= esc($title) ?></a></li>
          <?php endforeach ?>
        </ol>
      </nav>
    <?php endif ?>

    <div class="prose legal-text"><?= $page->text()->tiptapText() ?></div>
  </article>
<?php endsnippet() ?>
