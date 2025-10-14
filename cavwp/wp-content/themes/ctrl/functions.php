<?php

namespace ctrl;

add_action('wp_loaded', 'ctrl\load_theme');
function load_theme(): void
{
   if (!function_exists('cav_autoloader')) {
      return;
   }

   $AutoLoader = \cav_autoloader();
   $AutoLoader->add_namespace('ctrl', implode(DIRECTORY_SEPARATOR, [__DIR__, 'classes']));

   new Register();
   new Tasks_Page();
}

add_action('init', function() {
   add_theme_support('post-formats', ['gallery', 'video', 'audio', 'aside']);

   add_post_type_support('chapter', 'post-formats');
}, 11);

include_once 'classes/CAV_Entity_Rest_API.php';
