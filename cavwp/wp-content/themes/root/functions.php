<?php

namespace cav;

add_action('wp_loaded', 'cav\load_theme');
function load_theme(): void
{
   if (!function_exists('cav_autoloader')) {
      return;
   }

   $AutoLoader = \cav_autoloader();
   $AutoLoader->add_namespace('cav', implode(DIRECTORY_SEPARATOR, [__DIR__, 'classes']));
}

add_action('pre_get_posts', 'cav\set_query');
function set_query($query)
{
   if (empty($query->query['page']) && !empty($query->query['name'])) {
      $query->set('post_type', 'shortlink');
   }
}
