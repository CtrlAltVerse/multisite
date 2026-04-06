<?php

use ctrl\Hector\Prices;

$image_sizes = [
   'medium' => 'Miniatura',
   'amazon' => 'KDP/Apple',
   'kobo'   => 'Kobo/Google',
];

$products = \wc_get_products([
   'type'           => 'grouped',
   'posts_per_page' => -1,
   'orderby'        => 'title',
   'order'          => 'ASC',
]);

$Prices = new Prices();

?>
<style>
   .price-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.25rem;
      list-style: none;
      margin-top: 0.25rem;
      margin-bottom: 0;

      li {
         white-space: nowrap;
         padding: 0.25rem 0.5rem;
         border: 1px solid #666;
      }
   }
</style>

<div class="wrap">
   <h1>
      <?php echo get_admin_page_title(); ?>
   </h1>
   <h2>Digital</h2>
   <table class="widefat striped">
      <thead>
         <tr>
            <th style="width: 15%">Produto</th>
            <th style="width: 9%">Tamanho</th>
            <?php foreach (HECTOR_EPUB_FORMATS as $store => $data) {
               echo '<th style="width: 19%">' . $data['label'] . '</th>';
            } ?>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($products as $product) { ?>
         <?php $product_ID = $product->get_id(); ?>
         <tr>
            <th>
               <strong><a
                     href="<?php echo get_edit_post_link($product_ID); ?>">
                     <?php echo apply_filters('the_title', $product->get_name(), $product_ID); ?>
                  </a></strong><br />
               <em>
                  <a
                     href="<?php echo admin_url("edit.php?post_type=chapter&book={$product_ID}"); ?>">Capítulos</a>
                  &bull;
                  <a
                     href="<?php echo admin_url("edit.php?post_type=product&book={$product_ID}"); ?>">Lojas</a>
               </em>
            </th>
            <td>
               <?php $usd = book_numbers($product_ID); ?>
            </td>
            <?php foreach (HECTOR_EPUB_FORMATS as $store => $data) {
               $prices = $Prices->get_prices($product_ID, array_keys($data['currencies']));

               if (empty($prices)) {
                  echo '<td></td>';
                  continue;
               }

               $code = '';
               echo <<<HTML
               <td>
               <button type="button" class="button" onclick="navigator.clipboard.writeText(document.getElementById('code-{$product_ID}-{$store}').value)">Copiar código</button>
               <ul class="price-list">
               HTML;

               foreach ($prices as $currency => $price) {
                  $code .= "document.querySelectorAll('{$data['currencies'][$currency]}').forEach(input => input.value = '{$price}');";
                  echo "<li>{$currency} {$price}</li>";
               }
               echo <<<HTML
               </ul>
               <textarea id="code-{$product_ID}-{$store}" style="display:none">{$code}</textarea>
               </td>
               HTML;
            } ?>
         </tr>
         <?php } ?>
      </tbody>
   </table>
</div>
<?php

function book_numbers($product_ID)
{
   $chars_count = get_post_meta($product_ID, 'chars_count', true);
   $words_count = get_post_meta($product_ID, 'words_count', true);

   if (!empty($chars_count)) {
      $chars_count = (int) $chars_count;

      echo number_format($chars_count) . ' caracteres';
   }

   if (!empty($chars_count) && !empty($words_count)) {
      echo '<br />';
   }

   if (!empty($words_count)) {
      echo number_format((int) $words_count) . ' palavras';
   }

   if (empty($chars_count)) {
      return 0;
   }
}

?>
