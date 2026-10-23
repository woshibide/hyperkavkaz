<?php

use Kirby\Toolkit\V;

/**
 * Newsletter sign-up.
 * Not connected to a provider yet: valid addresses are accepted and discarded.
 *
 * No CSRF token on purpose: the form sits in the footer of every page,
 * and a token would start a session for every visitor and disable the page cache.
 * The request changes no account state; the provider's double opt-in is the real guard.
 */
return function ($kirby) {
  $email = '';
  $error = null;
  $done  = false;

  if ($kirby->request()->is('POST')) {
    $email = trim((string)get('email', ''));

    if (empty(get('website')) === false) {
      // Honeypot filled in: a bot. Pretend it worked.
      $email = '';
      $done  = true;
    } elseif (V::email($email) === false) {
      $error = 'Enter a valid email address, like name@example.com.';
    } else {
      // TODO: send $email to the newsletter provider once one is chosen.
      $email = '';
      $done  = true;
    }
  }

  return [
    'email' => $email,
    'error' => $error,
    'done'  => $done,
  ];
};
