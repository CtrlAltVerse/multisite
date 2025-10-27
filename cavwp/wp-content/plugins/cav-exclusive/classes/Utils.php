<?php

namespace cavEx;

use cavWP\Utils as CavWPUtils;

class Utils
{
   public static function set_auth_cookie($user_ID)
   {
      $expiration = time() + apply_filters('auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user_ID, true);
      $expire = $expiration + (12 * HOUR_IN_SECONDS);
      $secure = is_ssl();
      $secure_logged_in_cookie = $secure && 'https' === parse_url(get_option('home'), PHP_URL_SCHEME);
      $secure = apply_filters('secure_auth_cookie', $secure, $user_ID);
      $secure_logged_in_cookie = apply_filters('secure_logged_in_cookie', $secure_logged_in_cookie, $user_ID, $secure);

      if ($secure) {
         $auth_cookie_name = SECURE_AUTH_COOKIE;
         $scheme           = 'secure_auth';
      } else {
         $auth_cookie_name = AUTH_COOKIE;
         $scheme           = 'auth';
      }

      $manager = \WP_Session_Tokens::get_instance($user_ID);
      $token   = $manager->create($expiration);

      $auth_cookie      = wp_generate_auth_cookie($user_ID, $expiration, $scheme, $token);
      $logged_in_cookie = wp_generate_auth_cookie($user_ID, $expiration, 'logged_in', $token);

      do_action('set_auth_cookie', $auth_cookie, $expire, $expiration, $user_ID, $scheme, $token);
      do_action('set_logged_in_cookie', $logged_in_cookie, $expire, $expiration, $user_ID, 'logged_in', $token);

      if (! apply_filters('send_auth_cookies', true, $expire, $expiration, $user_ID, $scheme, $token)) {
         return;
      }

      $sites = get_blogs_of_user($user_ID);

      foreach ($sites as $site) {
         $domain = CavWPUtils::clean_domain($site->siteurl);

         setcookie($auth_cookie_name, $auth_cookie, $expire, PLUGINS_COOKIE_PATH, $domain, $secure, true);
         setcookie($auth_cookie_name, $auth_cookie, $expire, ADMIN_COOKIE_PATH, $domain, $secure, true);
         setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, COOKIEPATH, $domain, $secure_logged_in_cookie, true);

         if (COOKIEPATH !== SITECOOKIEPATH) {
            setcookie(LOGGED_IN_COOKIE, $logged_in_cookie, $expire, SITECOOKIEPATH, $domain, $secure_logged_in_cookie, true);
         }
      }
   }
}
