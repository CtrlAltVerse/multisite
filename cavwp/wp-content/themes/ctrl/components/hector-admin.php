<?php

use ctrl\Hector\Utils;

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

?>
<div class="wrap">
   <h1>
      <?php esc_html_e('Hector', 'ctrl'); ?>
   </h1>
   <h2>Download de Arquivos</h2>
   <table class="widefat striped">
         <thead>
         <tr>
            <th style="width: 15%">Produtos</th>
            <th style="width: 18%">Capas</th>
            <th style="width: 9%">Números</th>
            <th style="width: 30%">EPUBs</th>
            <th style="width: 19%">HTML</th>
            <th style="width: 9%">PDF</th>
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
               <?php foreach ($image_sizes as $size => $label) { ?>
               <?php $image_url = get_the_post_thumbnail_url($product_ID, $size); ?>
               <?php if ($image_url) {?>
               <a class="button"
                  href="<?php echo esc_url($image_url); ?>"
                  download="<?php basename($image_url); ?>"
                  target="_blank">
                  <?php echo esc_html($label); ?>
               </a>
               <?php } ?>
               <?php }?>
            </td>
            <td>
               <?php book_numbers($product_ID); ?>
            </td>
            <?php foreach (['epub', 'html', 'pdf'] as $type) { ?>
            <td>
               <?php book_buttons($product_ID, $type, $args); ?>
            </td>
            <?php } ?>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <form method="post" action="options.php">
      <?php

      $value = get_option($args['option_key'], '');
$option      = esc_attr($args['option_key']);
$value       = esc_textarea($value);

?>
      <h2>Style</h2>
      <?php settings_fields('hector'); ?>
      <textarea id="<?php echo $option; ?>"
                name="<?php echo $option; ?>"
                rows="40"><?php echo $value; ?></textarea>
      <?php submit_button(); ?>
   </form>
</div>
<?php

function book_numbers($product_ID)
{
   $chars_count = get_post_meta($product_ID, 'chars_count', true);
   $words_count = get_post_meta($product_ID, 'words_count', true);

   if (!empty($chars_count)) {
      echo number_format((int) $chars_count) . ' caracteres';
   }

   if (!empty($chars_count) && !empty($words_count)) {
      echo '<br />';
   }

   if (!empty($words_count)) {
      echo number_format((int) $words_count) . ' palavras';
   }
}

function book_buttons($product_ID, $type, $args)
{
   $parts = get_post_meta($product_ID, 'parts', true);

   if (empty($parts)) {
      return;
   }

   switch ($type) {
      case 'epub':
         $formats = HECTOR_EPUB_FORMATS;
         break;

      case 'pdf':
         $formats = HECTOR_PDF_FORMATS;
         break;

      case 'html':
         $formats = HECTOR_HTML_FORMATS;
         break;

      default:
         break;
   }

   ?>
<button class="button" type="button"
        data-file="<?php echo esc_attr($product_ID); ?>"
        data-type="<?php echo esc_attr($type); ?>">
   Gerar
</button>
<?php

   $files = glob(HECTOR_FOLDER . Utils::get_filename($product_ID) . '.' . $type);

   if (empty($files)) {
      return;
   }

   foreach ($files as $file) {
      $filename = basename($file);

      foreach ($formats as $key => $data) {
         if (!str_contains($filename, $key)) {
            continue;
         }
         ?>
<a class="button"
   download="<?php echo esc_attr($filename); ?>"
   href="<?php echo $args['ajax_url'] . '?' . http_build_query([
      'action' => 'hector_download_file',
      'file'   => $filename,
   ]); ?>">
   <?php echo esc_html($data['label']); ?>
</a>
<?php
      }
   }
}

?>
