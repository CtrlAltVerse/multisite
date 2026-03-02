<?php

global $product;

$post_thumbnail_id = $product->get_image_id();

$html = '';

if ($post_thumbnail_id) {
   $html = wc_get_gallery_image_html($post_thumbnail_id, true);
}

?>
<div class="woocommerce-product-gallery min-h-120" data-columns="1"
     style="opacity: 0; transition: opacity .25s ease-in-out;">
   <div class="woocommerce-product-gallery__wrapper">
      <?php

echo apply_filters('woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id);

do_action('woocommerce_product_thumbnails');
?>
   </div>
</div>
