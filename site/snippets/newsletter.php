<?php
/**
 * Newsletter sign-up form. Posts to the newsletter page, whose controller handles it.
 *
 * @var string      $email
 * @var string|null $error
 * @var bool        $done
 */
$email ??= '';
$error ??= null;
$done  ??= false;

if (!$newsletter = page('newsletter')) {
  return;
}
?>
<section id="newsletter" aria-labelledby="newsletter-title">
  <h2 id="newsletter-title">Newsletter</h2>
  <?php if ($done): ?>
    <p role="status">Thanks. You're on the list.</p>
  <?php else: ?>
    <form method="post" action="<?= $newsletter->url() ?>#newsletter">
      <label for="newsletter-email">Email</label>
      <input
        id="newsletter-email"
        type="email"
        name="email"
        autocomplete="email"
        spellcheck="false"
        required
        value="<?= esc($email, 'attr') ?>"
        <?= $error ? 'aria-invalid="true"' : '' ?>
        aria-describedby="newsletter-error"
      >
      <div class="visually-hidden" aria-hidden="true">
        <label for="newsletter-website">Leave this field empty</label>
        <input id="newsletter-website" type="text" name="website" tabindex="-1" autocomplete="off">
      </div>
      <button type="submit">Subscribe</button>
      <p id="newsletter-error" aria-live="polite"><?= esc($error ?? '') ?></p>
    </form>
  <?php endif ?>
</section>
