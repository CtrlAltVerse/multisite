<?php

namespace ctrl\Hector;

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

      if (empty($_POST['type']) || empty($_POST['book_id']) || !is_numeric($_POST['book_id']) || !in_array($_POST['type'], ['epub', 'pdf', 'html'])) {
         return wp_send_json_error([], 403);
      }

      $book_id = (int) $_POST['book_id'];
      $type    = (string) $_POST['type'];

      $book = new Book_Prep($book_id);

      if ('epub' === $type) {
         $filenames = $book->make_epub();
      }

      if ('pdf' === $type) {
         $filenames = $book->make_pdf();
      }

      if ('html' === $type) {
         $filenames = $book->make_html();
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
            $('button[data-file]').on('click', function() {

               const button = $(this);
               const bookId = button.data('file')
               const type = button.data('type')

               button.prop('disabled', true).text('Gerando...');

               $.post(ajaxUrl, {
                  action: 'hector_generate_book',
                  type,
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
      get_component('hector-admin', [
         'option_key' => $this->option_key,
         'ajax_url'   => $this->ajax_url,
      ]);
   }
}
?>
