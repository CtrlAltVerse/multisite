<?php

/*
 * Plugin Name:       CAV Global
 * Description:       For CAV Network.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            CtrlAltVerse
 * Author URI:        https://ctrl.altvers.net/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cav_global
 * Domain Path:       /languages
 */

class CAV_Global
{
   public function __construct()
   {
      add_action('wp_dashboard_setup', [$this, 'register_widget']);
      add_action('wp_user_dashboard_setup', [$this, 'register_widget']);
      add_action('wp_network_dashboard_setup', [$this, 'register_widget']);

      add_action('after_setup_theme', [$this, 'register_menu']);

      if (wp_get_environment_type() === 'local') {
         add_filter(
            'acf/settings/show_admin',
            'acf-tools' === ($_GET['page'] ?? false) ? '__return_true' : '__return_false',
         );
      }
   }

   public function register_menu()
   {
      if (get_current_blog_id() !== 1) {
         return;
      }

      register_nav_menu('admin_links', 'CAV Admin Links');
   }

   public function register_widget()
   {
      wp_add_dashboard_widget(
         'cav_global_links',
         'Links',
         [$this, 'render_widget'],
      );
   }

   public function render_widget()
   {
      switch_to_blog(1);

      echo '<div style="font-size:125%">';

      wp_nav_menu([
         'menu'      => 'admin_links',
         'container' => '',
      ]);

      echo '</div>';

      restore_current_blog();
   }
}

new \CAV_Global();
