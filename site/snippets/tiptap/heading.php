<?php
/**
 * Overrides the Tiptap plugin's heading: level 2 headings get an id from their text,
 * so they can be linked to (the contents list on legal pages, LegalPage::articles()).
 */
$level = max(1, min(6, (int)($attrs['level'] ?? 1)));
$id = $level === 2 ? Str::slug(html_entity_decode(strip_tags($content))) : '';
?>
<h<?= $level ?><?= $id !== '' ? ' id="' . $id . '"' : '' ?>><?= $content ?></h<?= $level ?>>
