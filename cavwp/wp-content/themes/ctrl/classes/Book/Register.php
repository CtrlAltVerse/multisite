<?php

namespace ctrl\Book;

class Register
{
   public function __construct()
   {
      add_filter('acf/fields/post_object/query/name=part', [$this, 'filter_chapters'], 10, 3);
      add_filter('acf/load_field/name=role', [$this, 'set_contributors_choices']);
   }

   public function filter_chapters($field_args, $_field, $product_ID)
   {
      $field_args['orderby']    = ['menu_order' => 'ASC', 'date' => 'DESC'];
      $field_args['meta_query'] = [[
         'key'   => 'book',
         'value' => $product_ID,
      ]];

      $spine = get_field('spine', $product_ID);

      if (!empty($spine)) {
         foreach ($spine as $item) {
            if ('chapter' !== $item['type']) {
               continue;
            }
            $exclude[] = $item['part'];
         }
         $field_args['exclude'] = $exclude;
      }

      return $field_args;
   }

   public function set_contributors_choices($field)
   {
      $field['choices'] = Utils::get_roles();

      return $field;
   }
}
