<?php

namespace ctrl\Product;

class Register
{
   public function __construct()
   {
      add_action('template_redirect', [$this, 'check_product_children']);
      add_action('wp_enqueue_scripts', [$this, 'remove_image_zoom_support']);

      add_filter('the_title', [$this, 'set_product_title'], 10, 2);
      add_filter('woocommerce_search_products_post_statuses', [$this, 'set_search_product_statuses'], 10, 2);

      add_filter('wp_insert_post_data', [$this, 'on_before_save_product'], 10, 2);
      add_action('save_post_product', [$this, 'on_save_product']);

      add_filter('woocommerce_gallery_image_size', [$this, 'set_gallery_image_size']);
   }

   public function check_product_children()
   {
      global $post;

      if (empty($post)) {
         return;
      }

      if ('product' !== $post->post_type) {
         return;
      }

      $types = get_the_terms($post->ID, 'product_type');

      foreach ($types as $type) {
         if ('grouped' === $type->slug) {
            return;
         }
      }

      $parent = (int) get_post_meta($post->ID, '_product_parent', true);

      if (empty($parent)) {
         return;
      }

      if (wp_safe_redirect(get_permalink($parent))) {
         exit;
      }
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

   public function remove_image_zoom_support()
   {
      remove_theme_support('wc-product-gallery-zoom');
   }

   public function set_gallery_image_size()
   {
      return 'medium';
   }

   public function set_product_title(string $title, int $post_ID = 0): string
   {
      if (empty($post_ID) || !is_admin()) {
         return $title;
      }

      global $pagenow;

      if ('hector' !== ($_GET['page'] ?? '') && ('edit.php' !== $pagenow || get_post_type($post_ID) !== 'product')
      ) {
         return $title;
      }

      $product_main = wc_get_product($post_ID);

      if ($product_main->is_type('grouped')) {
         $subtitle = get_post_meta($post_ID, 'subtitle', true);

         if (empty($subtitle)) {
            return $title;
         }

         return $title . ': “' . $subtitle . '”';
      }

      $product_parent = get_post_meta($post_ID, '_product_parent', true);

      if (empty($product_parent)) {
         return $title;
      }

      $product_parent = wc_get_product($product_parent);

      return '— ' . $product_parent->get_name() . ' > ' . $title;
   }

   public function set_search_product_statuses($post_statuses)
   {
      return current_user_can('administrator') ? ['private', 'publish', 'draft', 'future', 'pending'] : $post_statuses;
   }
}
