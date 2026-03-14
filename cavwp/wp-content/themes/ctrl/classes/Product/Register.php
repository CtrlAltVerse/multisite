<?php

namespace ctrl\Product;

use WC_Product_Attribute;
use WC_Product_External;
use WC_Product_Simple;

class Register
{
   public function __construct()
   {
      add_action('admin_init', [$this, 'wc_set_brands_off'], 9);

      add_action('restrict_manage_posts', [$this, 'filter_book_children_register']);
      add_action('pre_get_posts', [$this, 'filter_book_children_query']);

      add_action('template_redirect', [$this, 'check_product_children']);
      add_action('wp_enqueue_scripts', [$this, 'remove_image_zoom_support']);

      add_filter('the_title', [$this, 'set_product_title'], 10, 2);
      add_filter('woocommerce_search_products_post_statuses', [$this, 'set_search_product_statuses'], 10, 2);

      add_filter('wp_insert_post_data', [$this, 'on_before_save_product'], 10, 2);
      add_action('save_post_product', [$this, 'on_save_product']);
      add_action('save_post_product', [$this, 'on_create_product'], 10, 3);

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

   public function filter_book_children_query($query)
   {
      if (!is_admin() || !$query->is_main_query()) {
         return;
      }

      global $pagenow;

      if ('edit.php' !== $pagenow) {
         return;
      }

      if ($query->get('post_type') !== 'product') {
         return;
      }

      if (!empty($_GET['book'])) {
         $book_ID = (int) $_GET['book'];

         $query->set('meta_query', [
            [
               'key'     => '_product_parent',
               'compare' => '=',
               'value'   => $book_ID,
            ],
         ]);
      }
   }

   public function filter_book_children_register($post_type)
   {
      if ('product' !== $post_type) {
         return;
      }

      $selected = isset($_GET['book']) ? (int) ($_GET['book']) : '';

      $books = \wc_get_products([
         'posts_per_page' => -1,
         'orderby'        => 'title',
         'order'          => 'ASC',
         'product_type'   => 'grouped',
      ]);

      echo '<select name="book">';
      echo '<option value="">Filtrar por livro</option>';

      foreach ($books as $book) {
         printf(
            '<option value="%s" %s>%s</option>',
            $book->get_id(),
            selected($selected, $book->get_id(), false),
            apply_filters('the_title', $book->get_name(), $book->get_id()),
         );
      }

      echo '</select>';
   }

   public function on_before_save_product($data, $args)
   {
      if ('product' !== $args['post_type']) {
         return $data;
      }

      if (empty($_POST['grouped_products'])) {
         return $data;
      }

      $product = wc_get_product($args['ID']);

      if (is_bool($product) || empty($product)) {
         return $data;
      }

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

   public function on_create_product($product_ID, $_post, $update)
   {
      if (false === $update) {
         return;
      }

      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
         return;
      }

      $product = wc_get_product($product_ID);

      if (!$product->is_type('grouped')) {
         return;
      }

      $children_current = $product->get_children();

      $formats = array_merge(
         array_map(function($format) {
            $format['product_type'] = 'external';
            $format['product_cat']  = 'digital';

            return $format;
         }, HECTOR_EPUB_FORMATS),
         array_map(function($format) {
            $format['product_cat'] = 'print';

            return $format;
         }, HECTOR_HTML_FORMATS),
         [
            'web' => [
               'product_name' => 'Leitura online',
               'product_type' => 'simple',
               'product_cat'  => 'digital',
            ],
         ],
      );

      if (count($children_current) >= count($formats)) {
         return;
      }

      $children = array_map(function($_child_ID) {
         $_product   = wc_get_product($_child_ID);
         $attributes = $_product->get_attributes();

         if (empty($attributes['pa_store'])) {
            return false;
         }

         $term = get_term($attributes['pa_store']['options'][0], 'pa_store');

         return $term->slug;
      }, $children_current);

      $children_new = [];

      foreach ($formats as $key => $data) {
         if (in_array($key, $children)) {
            continue;
         }

         $product_child = match ($data['product_type']) {
            'simple'   => new WC_Product_Simple(),
            'external' => new WC_Product_External(),
         };

         $product_child->set_name($data['product_name']);
         $product_child->set_slug($product->get_slug() . '-' . $key);
         $product_child->set_status('draft');
         $product_child->set_catalog_visibility('hidden');
         $product_child->set_reviews_allowed(false);

         $term = get_term_by('slug', $key, 'pa_store')->term_id;

         $attribute = new WC_Product_Attribute();
         $attribute->set_id(wc_attribute_taxonomy_id_by_name('pa_store'));
         $attribute->set_name('pa_store');
         $attribute->set_options([$term]);
         $attribute->set_visible(false);
         $attribute->set_variation(false);

         $product_child->set_attributes(['pa_store' => $attribute]);

         $category = get_term_by('slug', $data['product_cat'], 'product_cat')->term_id;
         $product_child->set_category_ids([$category]);

         $price = match ($data['product_cat']) {
            'digital' => 7.99,
            'print'   => 29.99,
         };
         $product_child->set_regular_price($price);


         if ('br' === $key) {
            $product_child->set_stock_status('instock');
            $product_child->set_manage_stock(true);
            $product_child->set_stock_quantity(40);
            $product_child->set_weight(300);
            $product_child->set_length(2);
            $product_child->set_width(16);
            $product_child->set_height(23);
         }

         if ('web' === $key) {
            $product_child->set_sold_individually(true);
            $product_child->set_virtual(true);
         }

         $child_ID       = $product_child->save();
         $children_new[] = $child_ID;

         update_post_meta($child_ID, '_product_parent', $product_ID);
      }

      $_POST['grouped_products'] = array_merge($children_current, $children_new);
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

      if (is_bool($product_parent) || empty($product_parent)) {
         return $title;
      }

      return '— ' . $product_parent->get_name() . ' > ' . $title;
   }

   public function set_search_product_statuses($post_statuses)
   {
      return current_user_can('administrator') ? ['private', 'publish', 'draft', 'future', 'pending'] : $post_statuses;
   }

   public function wc_set_brands_off()
   {
      update_option('wc_feature_woocommerce_brands_enabled', 'no');
      unregister_taxonomy('product_brand');
   }
}
