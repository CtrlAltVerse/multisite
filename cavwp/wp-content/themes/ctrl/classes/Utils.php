<?php

namespace ctrl;

final class Utils
{
   public static function get_chapters($book)
   {
      return get_posts([
         'post_type'   => 'chapter',
         'post_status' => 'any',
         'nopaging'    => true,
         'orderby'     => 'menu_order',
         'order'       => 'ASC',
         'meta_query'  => [[
            'key'   => 'book',
            'value' => $book,
         ]],
      ]);
   }
}
