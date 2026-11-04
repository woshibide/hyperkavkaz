<?php
/**
 * Newsletter sign-up form. Posts to the newsletter page, whose controller handles it.
 *
 * @var string      $email
 * @var string|null $error
 * @var bool        $done
 * @var bool        $heading Whether to show the "Newsletter" heading and the one-line introduction;
 *                           off on the newsletter page, whose title and text say it.
 */
$email   ??= '';
$error   ??= null;
$done    ??= false;
$heading ??= true;

if (!$newsletter = page('newsletter')) {
  return;
}
?>
<section class="newsletter" id="newsletter" <?= $heading ? 'aria-labelledby="newsletter-title"' : 'aria-label="Newsletter sign-up"' ?>>
  <?php if ($heading): ?>
    <h2 id="newsletter-title">Newsletter</h2>
    <p>New releases and events, by email. Unsubscribe any time.</p>
  <?php endif ?>
  <?php if (option('hyperkavkaz.preview')): ?>
    <p>Sign-up opens when the site launches.</p>
    <div class="newsletter-field">
      <input type="email" aria-label="Email address" disabled>
      <button type="button" disabled>Subscribe</button>
    </div>
  <?php elseif ($done): ?>
    <p role="status">Thanks. You're on the list.</p>
  <?php else: ?>
    <form method="post" action="<?= $newsletter->url() ?>#newsletter">
      <label for="newsletter-email">Email address</label>
      <div class="newsletter-field">
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
        <button type="submit">Subscribe</button>
      </div>
      <div class="visually-hidden" aria-hidden="true">
        <label for="newsletter-website">Leave this field empty</label>
        <input id="newsletter-website" type="text" name="website" tabindex="-1" autocomplete="off">
      </div>
      <p class="newsletter-error" id="newsletter-error" aria-live="polite"><?= esc($error ?? '') ?></p>
    </form>
  <?php endif ?>
</section>
