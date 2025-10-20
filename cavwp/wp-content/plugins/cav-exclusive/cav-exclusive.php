<?php

namespace cavEx;

/*
 * Plugin Name:       CAV Exclusive
 * Description:       Integrações globais para CAV sites.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      8.0
 * Author:            CtrlAltVersœ
 * Author URI:        https://ctrl.altvers.net/
 * License:           GPL v3 or later
 * Text Domain:       cavex
 */

define('CAV_EX_FILE', __FILE__);

add_action('wp_loaded', 'cavEx\load_theme');
function load_theme(): void
{
   if (!function_exists('cav_autoloader')) {
      return;
   }

   $AutoLoader = \cav_autoloader();
   $AutoLoader->add_namespace('cavEx', implode(DIRECTORY_SEPARATOR, [__DIR__, 'classes']));

   new Rewards\Register();
   new Shortlink\Register();
}
