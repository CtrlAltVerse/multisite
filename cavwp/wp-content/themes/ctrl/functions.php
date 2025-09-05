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
}
