<?php

namespace ctrl;

use cavWP\Utils;

final class Register
{
   public function __construct()
   {
      add_action('wp_enqueue_scripts', [$this, 'handle_assets']);
      add_filter('get_custom_logo', [$this, 'set_logo']);
   }

   public function handle_assets()
   {
      wp_enqueue_style('main', get_theme_file_uri('assets/main.min.css'));
      wp_enqueue_script('main', get_theme_file_uri('assets/main.min.js'), [], false, [
         'strategy' => 'defer',
      ]);
   }

   public function set_logo($logo)
   {
      if (!empty($logo)) {
         return $logo;
      }

      return Utils::render_svg(get_template_directory() . '/assets/CtrlAltVerso.svg');
   }
}
