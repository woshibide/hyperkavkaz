<?php
/**
 * Numbered pagination: previous, up to 5 page numbers around the current one, next.
 * A missing previous or next link keeps its space, so the numbers stay centred.
 *
 * @var Kirby\Toolkit\Pagination $pagination
 */
?>
<?php if ($pagination->hasPages()): ?>
  <nav class="pagination" aria-label="Pagination">
    <?php if ($pagination->hasPrevPage()): ?>
      <a href="<?= $pagination->prevPageURL() ?>" rel="prev">Previous</a>
    <?php else: ?>
      <span class="pagination-placeholder" aria-hidden="true">Previous</span>
    <?php endif ?>
    <ul>
      <?php foreach ($pagination->range(5) as $number): ?>
        <li><a href="<?= $pagination->pageURL($number) ?>" aria-label="Page <?= $number ?>"<?= $pagination->page() === $number ? ' aria-current="page"' : '' ?>><?= $number ?></a></li>
      <?php endforeach ?>
    </ul>
    <?php if ($pagination->hasNextPage()): ?>
      <a href="<?= $pagination->nextPageURL() ?>" rel="next">Next</a>
    <?php else: ?>
      <span class="pagination-placeholder" aria-hidden="true">Next</span>
    <?php endif ?>
  </nav>
<?php endif ?>
