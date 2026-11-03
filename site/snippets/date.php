<?php
/**
 * A date as 2026.10.23, the year-first order Japanese readers scan fastest.
 * With 'month' => true, only year and month: 2026.10 (for milestones).
 * Printed without a trailing newline, so punctuation can follow it directly.
 *
 * @var Kirby\Content\Field $date
 * @var bool|null $month
 */
$month ??= false;
echo '<time datetime="' . $date->toDate($month ? 'Y-m' : 'Y-m-d') . '">' . $date->toDate($month ? 'Y.m' : 'Y.m.d') . '</time>';
