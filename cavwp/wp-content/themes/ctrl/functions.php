<?php

namespace ctrl;

define('HECTOR_FOLDER', ABSPATH . 'hector' . DIRECTORY_SEPARATOR);
define('HECTOR_EPUB_STORES', [
   'amazon' => 'Amazon Kindle',
   'kobo'   => 'Kobo',
   'apple'  => 'Apple Books',
   'google' => 'Google Books',
]);
define('HECTOR_PDF_FORMATS', [
   'br' => 'Brasil',
   'us' => 'Internacional',
]);

add_action('wp_loaded', 'ctrl\load_theme');
function load_theme(): void
{
   if (!function_exists('cav_autoloader')) {
      return;
   }

   $AutoLoader = \cav_autoloader();
   $AutoLoader->add_namespace('ctrl', implode(DIRECTORY_SEPARATOR, [__DIR__, 'classes']));

   new Register();
   new Product\Register();
   new Chapter\Register();
   new Book\Register();
   new Book\Register_Admin();
}

add_action('init', function() {
   add_theme_support('post-formats', ['gallery', 'video', 'audio', 'aside']);

   add_post_type_support('chapter', 'post-formats');
}, 11);

include_once 'classes/CAV_Entity_Rest_API.php';
