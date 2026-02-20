<?php

namespace ctrl;

use cavWP\Utils;

final class Register
{
   public function __construct()
   {
      add_action('wp_enqueue_scripts', [$this, 'handle_assets']);
      add_action('wp_resource_hints', [$this, 'add_resources'], 10, 2);
      add_action('admin_init', [$this, 'register_image_sizes']);
      add_action('admin_init', [$this, 'remove_image_sizes'], 15);
      add_action('after_setup_theme', [$this, 'register_image_sizes']);
      add_action('after_setup_theme', [$this, 'remove_image_sizes'], 15);

      add_shortcode('wp_hierarchy', [$this, 'sc_wp_hierarchy']);

      add_filter('get_custom_logo', [$this, 'set_logo']);

      new tools\Register();
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
      wp_register_script('highlight', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/highlight.min.js');
      wp_register_style('highlight', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/styles/atom-one-dark.min.css');

      $languages = ['php', 'css', 'js', 'html'];

      foreach ($languages as $language) {
         wp_register_script('highlight-' . $language, 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/languages/' . $language . '.min.js');
      }

      $deps_js  = [];
      $deps_css = [];

      if (is_page('ganhando-xp') || is_singular('print')) {
         $deps_js[] = 'rewards';
      }

      if (is_single()) {
         $deps_css[] = 'highlight';
         $deps_js[]  = 'highlight';

         foreach ($languages as $language) {
            $deps_js[] = 'highlight-' . $language;
         }
      }

      wp_enqueue_style('main', get_theme_file_uri('assets/main.min.css'), $deps_css);
      wp_enqueue_script('main', get_theme_file_uri('assets/main.min.js'), $deps_js, false, [
         'strategy' => 'defer',
      ]);

      $cav_template = get_query_var('cav', false);

      if ('links' !== $cav_template && !is_admin()) {
         remove_theme_support('custom-background');
      }
   }

   public function register_image_sizes()
   {
      add_image_size('amazon', 1600, 2560, true);
      add_image_size('apple', 1600, 2560, true);

      add_image_size('kobo', 1600, 2133, true);
      add_image_size('google', 1600, 2133, true);
   }

   public function remove_image_sizes()
   {
      remove_image_size('1536x1536');
      remove_image_size('2048x2048');
      remove_image_size('woocommerce_gallery_thumbnail');
      remove_image_size('woocommerce_single');
      remove_image_size('woocommerce_thumbnail');
   }

   public function sc_wp_hierarchy()
   {
      ob_start();

      get_component('sc-hierarchy');

      return ob_get_clean();
   }

   public function set_logo($logo)
   {
      if (!empty($logo)) {
         return $logo;
      }

      return Utils::render_svg(get_template_directory() . '/assets/CtrlAltVerso.svg');
   }
}
