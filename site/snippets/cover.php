<?php
/**
 * The page's cover image, if set. Renders nothing otherwise.
 *
 * @var Kirby\Cms\Page $page
 */
if (!$image = $page->cover()->toFile()) {
  return;
}
$version = $image->resize(1600);
?>
<img
  src="<?= $version->url() ?>"
  width="<?= $version->width() ?>"
  height="<?= $version->height() ?>"
  alt="<?= $image->alt()->esc('attr') ?>"
>
