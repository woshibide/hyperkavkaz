<?php
// An index by first letter, as in a record shop's racks.
// Initials outside A to Z (digits, symbols) are grouped under "#".
// Collection keys are lowercase, so letters are compared and anchored in lowercase.
$initial = function ($artist) {
  $letter = Str::lower(Str::substr($artist->title()->value(), 0, 1));
  return preg_match('/^[a-z]$/', $letter) ? $letter : '#';
};
$groups = $artists->group($initial);
$anchor = fn (string $letter) => 'letter-' . ($letter === '#' ? 'other' : $letter);
?>
<?php snippet('layout', slots: true) ?>
  <div class="column">
    <?php snippet('page-head', slots: true) ?>
      <?php if ($artists->isNotEmpty()): ?>
        <nav class="letter-index" aria-label="Artists by letter">
          <ul>
            <?php foreach ([...range('a', 'z'), '#'] as $letter): ?>
              <?php if ($groups->has($letter)): ?>
                <li><a href="#<?= $anchor($letter) ?>"><?= Str::upper($letter) ?></a></li>
              <?php elseif ($letter !== '#'): ?>
                <li aria-hidden="true"><span><?= Str::upper($letter) ?></span></li>
              <?php endif ?>
            <?php endforeach ?>
          </ul>
        </nav>
      <?php endif ?>
    <?php endsnippet() ?>

    <?php if ($artists->isEmpty()): ?>
      <p class="empty">No artists yet.</p>
    <?php else: ?>
      <?php foreach ($groups as $letter => $group): ?>
        <section class="artist-group" id="<?= $anchor($letter) ?>" aria-labelledby="<?= $anchor($letter) ?>-title">
          <h2 class="artist-group-letter" id="<?= $anchor($letter) ?>-title"><?= Str::upper($letter) ?></h2>
          <ul>
            <?php foreach ($group as $artist): ?>
              <?php $count = $artist->releases()->count() ?>
              <li>
                <a class="artist-entry" href="<?= $artist->url() ?>">
                  <span class="artist-entry-name"><?= $artist->title()->esc() ?></span>
                  <?php if ($artist->origin()->isNotEmpty() || $count > 0): ?>
                    <span class="artist-entry-meta">
                      <?= implode(', ', array_filter([
                        (string)$artist->origin()->esc(),
                        $count > 0 ? $count . ($count === 1 ? ' release' : ' releases') : null,
                      ])) ?>
                    </span>
                  <?php endif ?>
                </a>
              </li>
            <?php endforeach ?>
          </ul>
        </section>
      <?php endforeach ?>
    <?php endif ?>
  </div>
<?php endsnippet() ?>
