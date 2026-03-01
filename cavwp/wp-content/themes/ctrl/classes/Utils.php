<?php

namespace ctrl;

final class Utils
{
   public static function get_chapters($target_book)
   {
      return get_posts([
         'post_type'   => 'chapter',
         'post_status' => 'any',
         'nopaging'    => true,
         'orderby'     => 'menu_order',
         'order'       => 'ASC',
         'meta_query'  => [
            'relation' => 'OR',
            [
               'key'     => 'book',
               'compare' => 'LIKE',
               'value'   => "\"{$target_book}\"",
            ],
            [
               'key'     => 'book',
               'compare' => '=',
               'value'   => $target_book,
            ],
         ]]);
   }
}
