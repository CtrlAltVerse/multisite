<?php

namespace ctrl\Hector;

class Register
{
   public function __construct()
   {
      new Register_Admin();

      add_filter('acf/fields/post_object/query/name=part', [$this, 'filter_chapters'], 10, 3);
      add_filter('acf/load_field/name=role', [$this, 'set_contributors_choices']);

      add_filter('wp_get_attachment_image_src', [$this, 'set_attachment_url']);

      add_action('admin_init', [$this, 'add_styles']);
   }

   public function add_styles()
   {
      foreach (BLOCK_STYLES as $block => $styles) {
         foreach ($styles as $style) {
            register_block_style($block, $style);
         }
      }
   }

   public function filter_chapters($field_args, $_field, $product_ID)
   {
      $field_args['orderby']    = ['menu_order' => 'ASC', 'date' => 'ASC'];
      $field_args['meta_query'] = [
         'relation' => 'OR',
         [
            'key'     => 'book',
            'compare' => 'LIKE',
            'value'   => "\"{$product_ID}\"",
         ],
         [
            'key'     => 'book',
            'compare' => '=',
            'value'   => $product_ID,
         ]];

      $parts = get_field('parts', $product_ID);

      if (!empty($parts)) {
         foreach ($parts as $part) {
            foreach ($part['spine'] as $item) {
               $exclude[] = $item['part'];
            }
         }
         $field_args['exclude'] = $exclude;
      }

      return $field_args;
   }

   public function set_attachment_url($image)
   {
      $image_local_url  = home_url('/wp-content/uploads');
      $image_remote_url = 'https://ctrl.altvers.net/wp-content/uploads';

      return str_replace([$image_local_url, $image_remote_url], 'https://cdn.altvers.net', $image);
   }

   public function set_contributors_choices($field)
   {
      $field['choices'] = Utils::get_roles();

      return $field;
   }
}
