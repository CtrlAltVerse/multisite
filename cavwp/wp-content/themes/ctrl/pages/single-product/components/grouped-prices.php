<?php

global $product;

$lists = [false, true];

$show_add_to_cart_button = false;
$quantites_required      = false;

?>
<form class="cart grouped_form"
      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
      method="post" enctype='multipart/form-data'>
   <div class="flex flex-col gap-5">
      <?php foreach ($lists as $list) { ?>
      <div class="bg-westar-200/90 rounded">
      <ul class="flex">
         <?php

            $grouped_products = $product->get_children();

         foreach ($grouped_products as $product_child) {
            $product_child = wc_get_product($product_child);

            if ($product_child->is_type('external') !== $list) {
               continue;
            }

            if (empty($quantites_required)) {
               $quantites_required = ($product_child->is_purchasable() && !$product_child->has_options());
            }

            if (empty($show_add_to_cart_button)) {
               $show_add_to_cart_button = $product_child->is_in_stock();
            }

            echo '<li class="flex-1">';

            if ($product_child->is_type('external')) {
               /** @disregard */
               echo '<a class="flex flex-col items-center gap-1 size-full p-4" href="' . esc_url($product_child->get_product_url()) . '" target="_blank" rel="nofollow">';
            } else {
               echo '<label class="flex flex-col items-center gap-1 size-full p-4">';
            }

            if ($product_child->is_sold_individually()) {
               echo '<span class="sr-only">';

               if ($product_child->is_on_sale()) {
                  printf(
                     // translators: %1$s: Product name. %2$s: Sale price. %3$s: Regular price
                     esc_html__('Buy one of %1$s on sale for %2$s, original price was %3$s', 'woocommerce'),
                     esc_html($product_child->get_name()),
                     esc_html(wp_strip_all_tags(wc_price($product_child->get_price()))),
                     esc_html(wp_strip_all_tags(wc_price($product_child->get_regular_price()))),
                  );
               } else {
                  printf(
                     // translators: %1$s: Product name. %2$s: Product price
                     esc_html__('Buy one of %1$s for %2$s', 'woocommerce'),
                     esc_html($product_child->get_name()),
                     esc_html(wp_strip_all_tags(wc_price($product_child->get_price()))),
                  );
               }
               echo '</span>';
            }

            echo '<strong class="text-lg">' . esc_html($product_child->get_name()) . '</strong>';
            echo '<span class="grow flex flex-col justify-center items-center">' . $product_child->get_price_html() . '</span>';

            // . wc_get_stock_html($product_child);

            if (!$product_child->is_type('external')) {
               if ($product_child->is_sold_individually()) {
                  echo '<input type="checkbox" name="' . esc_attr('quantity[' . $product_child->get_id() . ']') . '" value="1" class="size-6" id="' . esc_attr('quantity-' . $product_child->get_id()) . '" />';
               } else {
                  woocommerce_quantity_input(
                     [
                        'input_name'  => 'quantity[' . $product_child->get_id() . ']',
                        'classes'     => 'bg-neutral-100 text-center text-xl',
                        'input_value' => isset($_POST['quantity'][$product_child->get_id()]) ? wc_stock_amount(wc_clean(wp_unslash($_POST['quantity'][$product_child->get_id()]))) : '',
                        'min_value'   => 0,
                        'max_value'   => $product_child->get_max_purchase_quantity(),
                        'placeholder' => '0',
                     ],
                  );
               }
            }

            if ($product_child->is_type('external')) {
               echo '</a>';
            } else {
               echo '</label>';
            }

            echo '</li>';
         }

         ?>
      </ul>
         <?php if ($quantites_required && $show_add_to_cart_button && $product_child->is_type('external') !== $list) { ?>
<div class="flex px-4 pb-2">
            <button type="submit" class="w-full py-2 px-4 bg-neutral-700 text-neutral-100 rounded mb-3">
               <?php echo esc_html($product->single_add_to_cart_text()); ?>
            </button>
         </div>
         <?php } ?>
         </div>
      <?php } ?>
   </div>

   <input type="hidden" name="add-to-cart"
          value="<?php echo esc_attr($product->get_id()); ?>" />

</form>
