<?php

namespace ctrl;

use cavWP\Utils;

final class Register
{
   public function __construct()
   {
      add_action('wp_enqueue_scripts', [$this, 'handle_assets']);
      add_action('wp_resource_hints', [$this, 'add_resources'], 10, 2);

      add_filter('get_custom_logo', [$this, 'set_logo']);
   }

   public function add_resources($urls, $type)
   {
      if ('preconnect' === $type) {
         $urls[] = [
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
         ];
      }

      return $urls;
   }

   public function handle_assets()
   {
      $deps = [];

      if (is_page('ganhando-xp') || is_singular('print')) {
         $deps[] = 'rewards';
      }

      wp_enqueue_style('main', get_theme_file_uri('assets/main.min.css'));
      wp_enqueue_script('main', get_theme_file_uri('assets/main.min.js'), $deps, false, [
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
