<?php

namespace ctrl\Product;

class Register
{
   public function __construct()
   {
      add_filter('the_title', [$this, 'set_title_children'], 10, 2);

      add_filter('wp_insert_post_data', [$this, 'on_before_save_product'], 10, 2);
      add_action('save_post_product', [$this, 'on_save_product']);
   }

   public function on_before_save_product($data, $args)
   {
      if ('product' !== $args['post_type']) {
         return $data;
      }

      if (empty($_POST['grouped_products'])) {
         return $data;
      }

      $product          = wc_get_product($args['ID']);
      $current_children = $product->get_children();
      $new_children     = $_POST['grouped_products'];
      $to_change        = array_diff($current_children, $new_children);

      if (!empty($to_change)) {
         foreach ($to_change as $child_ID) {
            delete_post_meta($child_ID, '_product_parent');
         }
      }

      return $data;
   }

   public function on_save_product($post_ID): void
   {
      $product = wc_get_product($post_ID);

      if (!$product->is_type('grouped')) {
         return;
      }

      $children = $_POST['grouped_products'] ?? [];

      if (empty($children)) {
         return;
      }

      foreach ($children as $child_ID) {
         \add_post_meta($child_ID, '_product_parent', $post_ID, true);
      }
   }

   public function set_title_children(string $title, int $post_ID): string
   {
      global $pagenow;

      if (
         !is_admin() || 'edit.php' !== $pagenow || get_post_type($post_ID) !== 'product'
      ) {
         return $title;
      }

      $product_parent = get_post_meta($post_ID, '_product_parent', true);

      if (empty($product_parent)) {
         return $title;
      }

      $product = wc_get_product($product_parent);

      return '— ' . $product->get_name() . ' > ' . $title;
   }
}
