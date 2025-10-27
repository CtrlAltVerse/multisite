<?php

namespace ctrl\tools;

class Register
{
   public function __construct()
   {
      add_action('wp_enqueue_scripts', [$this, 'handle_assets'], 5);
   }

   public function handle_assets()
   {
      if (!is_singular('tool') && !is_post_type_archive('tool')) {
         return;
      }

      wp_enqueue_script('tools', get_theme_file_uri('assets/tools.min.js'), [], false, [
         'strategy' => 'defer',
      ]);

      $tools = get_posts([
         'post_type'   => 'tool',
         'post_status' => 'publish',
         'order'       => 'ASC',
         'orderby'     => 'title',
         'nopaging'    => true,
      ]);

      $tools = array_map(fn($tool) => [
         'ID'          => $tool->ID,
         'title'       => $tool->post_title,
         'description' => $tool->post_excerpt,
         'link'        => get_permalink($tool->ID),
      ], $tools);

      wp_localize_script('tools', 'tools', ['list' => $tools]);
   }
}
