<?php /** @var Kirby\Toolkit\Pagination $pagination */ ?>
<?php if ($pagination->hasPages()): ?>
  <nav aria-label="Pagination">
    <?php if ($pagination->hasPrevPage()): ?>
      <a href="<?= $pagination->prevPageURL() ?>" rel="prev">Previous page</a>
    <?php endif ?>
    <?php if ($pagination->hasNextPage()): ?>
      <a href="<?= $pagination->nextPageURL() ?>" rel="next">Next page</a>
    <?php endif ?>
  </nav>
<?php endif ?>
