<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head') ?>
    <?php if (($links = $page->links()->toStructure())->isEmpty()): ?>
      <p class="empty">No links yet.</p>
    <?php else: ?>
      <ul class="link-stack">
        <?php foreach ($links as $item): ?>
          <?php $host = parse_url($item->link()->value(), PHP_URL_HOST) ?>
          <li>
            <a href="<?= $item->link()->esc('attr') ?>">
              <span class="link-stack-label"><?= $item->label()->esc() ?></span>
              <?php if (is_string($host)): ?>
                <span class="link-stack-host"><?= esc(preg_replace('/^www\./', '', $host)) ?></span>
              <?php endif ?>
            </a>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
