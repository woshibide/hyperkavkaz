<?php /** @var Kirby\Content\Field $date */ ?>
<time datetime="<?= $date->toDate('Y-m-d') ?>"><?= $date->toDate('j F Y') ?></time>
