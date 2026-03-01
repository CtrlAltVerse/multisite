<?php

global $product;

$post_thumbnail_id = $product->get_image_id();

?>
<div class="woocommerce-product-gallery min-h-120" data-columns="1" style="opacity: 0; transition: opacity .25s ease-in-out;">
   <div class="woocommerce-product-gallery__wrapper">
      <?php
        if ($post_thumbnail_id) {
           $html = wc_get_gallery_image_html($post_thumbnail_id, true);
        } else {
           $wrapper_classname = $product->is_type(\ProductType::VARIABLE) && !empty($product->get_visible_children()) && '' !== $product->get_price() ?
               'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
               'woocommerce-product-gallery__image--placeholder';
           $html = sprintf('<div class="%s">', esc_attr($wrapper_classname));
           $html .= sprintf('<img src="%s" alt="%s" class="wp-post-image" />', esc_url(wc_placeholder_img_src('woocommerce_single')), esc_html__('Awaiting product image', 'woocommerce'));
           $html .= '</div>';
        }

echo apply_filters('woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id);

do_action('woocommerce_product_thumbnails');
?>
   </div>
</div>
