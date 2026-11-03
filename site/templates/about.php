<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>

    <?php if ($page->lead()->isNotEmpty()): ?>
      <p class="lead"><?= $page->lead()->esc() ?></p>
    <?php endif ?>

    <?php if ($page->text()->isNotEmpty()): ?>
      <div class="prose"><?= $page->text()->tiptapText() ?></div>
    <?php endif ?>

    <?php if (($facts = $page->facts()->toStructure())->isNotEmpty()): ?>
      <section class="section" aria-labelledby="overview">
        <h2 class="section-title" id="overview">Overview</h2>
        <dl class="facts">
          <?php foreach ($facts as $fact): ?>
            <div>
              <dt><?= $fact->label()->esc() ?></dt>
              <dd><?= V::email($fact->value()->value()) ? Html::email($fact->value()->value()) : $fact->value()->esc() ?></dd>
            </div>
          <?php endforeach ?>
        </dl>
      </section>
    <?php endif ?>

    <?php if (($history = $page->history()->toStructure()->sortBy('date', 'asc'))->isNotEmpty()): ?>
      <section class="section" aria-labelledby="history">
        <h2 class="section-title" id="history">History</h2>
        <ol class="timeline">
          <?php foreach ($history as $item): ?>
            <li>
              <?php snippet('date', ['date' => $item->date(), 'month' => true]) ?>
              <p><?= $item->event()->esc() ?></p>
            </li>
          <?php endforeach ?>
        </ol>
      </section>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
