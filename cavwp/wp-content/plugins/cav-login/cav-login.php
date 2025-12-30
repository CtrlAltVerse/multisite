<?php

/*
 * Plugin Name:       CAV Login
 * Description:       Cria cookie durante login.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      8.0
 * Author:            CtrlAltVersœ
 * Author URI:        https://ctrl.altvers.net/
 * License:           GPL v3 or later
 * Text Domain:       cavlg
 */

final class CavLogin
{
   public $prefix = 'cav_logged';

   public function __construct()
   {
      add_action('wp_login', [$this, 'on_login']);
      add_action('wp_logout', [$this, 'on_logout']);
   }

   public function on_login()
   {
      $expire = time() + (14.5 * DAY_IN_SECONDS);
      setcookie($this->prefix, true, $expire, '/', '', is_ssl(), true);
   }

   public function on_logout()
   {
      $expire = -1 * DAY_IN_SECONDS;
      setcookie($this->prefix, false, $expire, '/', '', is_ssl(), true);
   }
}

add_action('wp_loaded', 'cav_login_load_theme');
function cav_login_load_theme(): void
{
   new \CavLogin();
}
