<?php

namespace ctrl\Book;

class Register_Admin
{
   private string $ajax_url = '';
   private $option_key      = 'cav_hector_epub_style';

   public function __construct()
   {
      $this->ajax_url = admin_url('admin-ajax.php');

      add_action('init', [$this, 'init']);

      add_action('admin_menu', [$this, 'register_page']);
      add_action('admin_init', [$this, 'register_setting']);
      add_action('admin_footer', [$this, 'footer_content']);
      add_action('admin_enqueue_scripts', [$this, 'enqueue_codemirror']);

      add_action('wp_ajax_hector_download_file', [$this, 'ajax_download_file']);
      add_action('wp_ajax_hector_generate_book', [$this, 'ajax_generate_book']);
   }

   public function ajax_download_file(): void
   {
      if (!current_user_can('edit_posts')) {
         wp_die('Unauthorized');
      }

      $file = sanitize_text_field($_GET['file'] ?? '');

      if (!$file) {
         wp_die('Invalid file');
      }

      $filepath = realpath(HECTOR_FOLDER . $file);

      if (!$filepath || !str_starts_with($filepath, realpath(HECTOR_FOLDER))) {
         wp_die('Invalid path');
      }

      if (!file_exists($filepath)) {
         wp_die('File not found');
      }

      header('Content-Description: File Transfer');

      if (pathinfo($file, PATHINFO_EXTENSION) === 'epub') {
         header('Content-Type: application/epub+zip');
      }

      if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
         header('Content-Type: application/pdf');
      }
      header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
      header('Content-Length: ' . filesize($filepath));
      header('Cache-Control: no-cache');
      header('Pragma: public');

      flush();
      readfile($filepath);
      exit;
   }

   public function ajax_generate_book()
   {
      if (!current_user_can('edit_posts')) {
         wp_die();
      }

      if (empty($_POST['type']) || empty($_POST['book_id']) || !is_numeric($_POST['book_id']) || !in_array($_POST['type'], ['epub', 'pdf'])) {
         return wp_send_json_error([], 403);
      }

      $book_id = (int) $_POST['book_id'];
      $type    = (string) $_POST['type'];

      $book = new Book($book_id);

      if ('epub' === $type) {
         $filenames = $book->make_epub();
      }

      if ('pdf' === $type) {
         $filenames = $book->make_pdf();
      }

      wp_send_json_success([
         'files' => $filenames,
      ]);
   }

   public function enqueue_codemirror(string $hook): void
   {
      if ('toplevel_page_hector' !== $hook) {
         return;
      }

      $settings = wp_enqueue_code_editor([
         'type' => 'text/css',
      ]);

      if (false === $settings) {
         return;
      }

      $content = 'jQuery(function($){ wp.codeEditor.initialize("' . $this->option_key . '", %s); });';

      wp_add_inline_script(
         'code-editor',
         sprintf(
            $content,
            wp_json_encode($settings),
         ),
      );

      wp_enqueue_script('wp-theme-plugin-editor');
      wp_enqueue_style('wp-codemirror');
   }

   public function footer_content()
   {
      global $pagenow;

      if (!$pagenow === 'admin.php' || 'hector' !== ($_GET['page'] ?? false)) {
         return;
      }

      ?>
<script>
         const ajaxUrl = '<?php echo esc_js($this->ajax_url); ?>';
         jQuery(function($) {
            $('button[data-pdf],button[data-epub]').on('click', function() {
               const button = $(this);
               const bookId = button.data('pdf') ?? button.data('epub');

               button.prop('disabled', true).text('Gerando...');

               $.post(ajaxUrl, {
                  action: 'hector_generate_book',
                  type: !!button.data('pdf') ? 'pdf' : 'epub',
                  book_id: bookId
               }).done(function() {
                  location.reload();
               });
            });
         });
      </script>
<?php
   }

   public function init()
   {
      register_block_style(
         'core/image',
         [
            'name'  => 'portrait',
            'label' => __('Retrato', 'ctrl'),
         ],
      );

      register_block_style(
         'core/separator',
         [
            'name'  => 'asterism',
            'label' => __('Asteriscos', 'ctrl'),
         ],
      );

      register_block_style(
         'core/separator',
         [
            'name'  => 'transition',
            'label' => __('Espaço vazio', 'ctrl'),
         ],
      );
   }

   public function register_page(): void
   {
      add_menu_page(
         'Hector',
         'Hector',
         'manage_options',
         'hector',
         [$this, 'render_page'],
         'dashicons-book',
         0,
      );
   }

   public function register_setting(): void
   {
      register_setting(
         'hector',
         $this->option_key,
      );
   }

   public function render_page(): void
   {
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
            <th style="width: 20%">Capas</th>
            <th style="width: 10%">Números</th>
            <th style="width: 35%">EPUBs</th>
            <th style="width: 20%">PDFs</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($products as $product) { ?>
         <tr>
            <th>
               <?php echo apply_filters('the_title', $product->get_name(), $product->get_id()); ?>
            </th>
            <td>
               <?php foreach ($image_sizes as $size => $label) {
                  $image_url = get_the_post_thumbnail_url($product->get_id(), $size);

                  if ($image_url) {
                     ?>
               <a class="button"
                  href="<?php echo esc_url($image_url); ?>"
                  download="<?php basename($image_url); ?>"
                  target="_blank">
                  <?php echo esc_html($label); ?>
               </a>
               <?php
                  }
               } ?>
            </td>
            <td>
               <?php

               $chars_count = get_post_meta($product->get_id(), 'chars_count', true);

            if (!empty($chars_count)) {
               echo $chars_count . ' caracteres';
            }

            $pages_count = get_post_meta($product->get_id(), 'pages_count', true);

            if (!empty($chars_count) && !empty($pages_count)) {
               echo '<br />';
            }

            if (!empty($pages_count)) {
               echo $pages_count . ' páginas';
            }

            ?>
            </td>
            <td>
               <?php if (!empty(get_post_meta($product->get_id(), 'parts', true))) { ?>
               <button class="button" type="button"
                       data-epub="<?php echo esc_attr($product->get_id()); ?>">
                  Gerar
               </button>
               <?php

               $files = glob(HECTOR_FOLDER . Utils::get_filename($product->get_id()) . '.epub');

                  if (!empty($files)) {
                     foreach ($files as $file) {
                        $filename = basename($file);

                        foreach (HECTOR_EPUB_STORES as $key => $title) {
                           if (str_contains($filename, $key)) {
                              ?>
               <a class="button"
                  download="<?php echo esc_attr($filename); ?>"
                  href="<?php echo $this->ajax_url . '?' . http_build_query([
                     'action' => 'hector_download_file',
                     'file'   => $filename,
                  ]); ?>">
                  <?php echo esc_html($title); ?>
               </a>
               <?php
                           }
                        }
                     }
                  }
               }
            ?>
            </td>
            <td>
               <?php if (!empty(get_post_meta($product->get_id(), 'parts', true))) { ?>
               <button class="button" type="button"
                       data-pdf="<?php echo esc_attr($product->get_id()); ?>">
                  Gerar
               </button>
               <?php

               $files = glob(HECTOR_FOLDER . Utils::get_filename($product->get_id()) . '.pdf');

                  if (!empty($files)) {
                     foreach ($files as $file) {
                        $filename = basename($file);

                        foreach (HECTOR_PDF_FORMATS as $key => $title) {
                           if (str_contains($filename, $key)) {
                              ?>
               <a class="button"
                  download="<?php echo esc_attr($filename); ?>"
                  href="<?php echo $this->ajax_url . '?' . http_build_query([
                     'action' => 'hector_download_file',
                     'file'   => $filename,
                  ]); ?>">
                  <?php echo esc_html($title); ?>
               </a>
               <?php
                           }
                        }
                     }
                  }
               }
            ?>
            </td>
         </tr>
         <?php } ?>
      </tbody>
   </table>
   <form method="post" action="options.php">
      <?php

      $value  = get_option($this->option_key, '');
      $option = esc_attr($this->option_key);
      $value  = esc_textarea($value);
      $submit = get_submit_button();

      ?>
      <h2> Style </h2>
      <?php settings_fields('hector');

      echo <<<HTML
         <textarea id="{$option}" name="{$option}" rows="40">{$value}</textarea>
         {$submit}
      HTML;

      ?>
   </form>
</div>
<?php
   }
}
?>
