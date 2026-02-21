<?php

namespace ctrl\Book;

use cavWP\Utils as CavWPUtils;

class Utils
{
   public static function get_filename($product_ID, $version = false)
   {
      if (empty($product_ID)) {
         return;
      }

      $product = wc_get_product($product_ID);

      if (empty($product)) {
         return;
      }

      $year   = $product->get_date_created()->date('Y');
      $title  = $product->get_slug();
      $author = '';

      $authors = get_field('authors', $product_ID);

      if (!empty($authors)) {
         foreach ($authors as $author) {
            $authors_names[] = get_the_author_meta('display_name', $author);
         }
         $author = CavWPUtils::parse_titles($authors_names);
      }

      if (!$version) {
         $version = '*';
      }

      return sanitize_file_name("{$year}-{$author}-{$title}") . '-' . $version . '.epub';
   }
}
