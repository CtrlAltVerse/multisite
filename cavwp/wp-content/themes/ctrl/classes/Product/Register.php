<?php

namespace ctrl\Product;

class Register
{
   private array $group_map = [];

   public function __construct()
   {
      add_action('pre_get_posts', [$this, 'reorder_products']);
      add_filter('the_title', [$this, 'indent_child_products'], 10, 2);
   }

   public function indent_child_products(string $title, int $post_id): string
   {
      global $pagenow;

      if (
         !is_admin() || 'edit.php' !== $pagenow || get_post_type($post_id) !== 'product'
      ) {
         return $title;
      }

      if (isset($this->group_map[$post_id])) {
         return '— ' . $title;
      }

      return $title;
   }

   public function reorder_products($query): void
   {
      if (
         !is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'product'
      ) {
         return;
      }

      $products = get_posts([
         'post_type'      => 'product',
         'posts_per_page' => -1,
         'orderby'        => 'menu_order title',
         'order'          => 'ASC',
         'post_status'    => 'any',
      ]);

      $ordered_ids = [];

      foreach ($products as $product_post) {
         $product = wc_get_product($product_post->ID);

         if (!$product) {
            continue;
         }

         if ($product->is_type('grouped')) {
            $ordered_ids[] = $product->get_id();

            $children = $product->get_children();

            foreach ($children as $child_id) {
               $ordered_ids[]              = $child_id;
               $this->group_map[$child_id] = $product->get_id();
            }
         } elseif (!isset($this->group_map[$product->get_id()])) {
            $ordered_ids[] = $product->get_id();
         }
      }

      if (!empty($ordered_ids)) {
         $query->set('post__in', $ordered_ids);
         $query->set('orderby', 'post__in');
      }
   }
}
