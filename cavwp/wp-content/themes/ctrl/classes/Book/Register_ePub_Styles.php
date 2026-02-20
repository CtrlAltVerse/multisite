<?php

namespace ctrl\Book;

class Register_ePub_Styles
{
   private string $option_name = 'cav_hector_epub_style';

   public function __construct()
   {
      add_action('init', [$this, 'init']);

      add_action('admin_menu', [$this, 'register_page']);
      add_action('admin_init', [$this, 'register_setting']);
      add_action('admin_enqueue_scripts', [$this, 'enqueue_codemirror']);

      /*
      .section-description
      .section-author
      .section-date
      */
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

      wp_add_inline_script(
         'code-editor',
         sprintf(
            'jQuery(function($){ wp.codeEditor.initialize("custom_style_editor", %s); });',
            wp_json_encode($settings),
         ),
      );

      wp_enqueue_script('wp-theme-plugin-editor');
      wp_enqueue_style('wp-codemirror');
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
         $this->option_name,
      );
   }

   public function render_page(): void
   {
      $value = get_option($this->option_name, '');

      ?>
<div class="wrap">
   <h1>ePub Style</h1>
   <form method="post" action="options.php">
      <?php settings_fields('hector'); ?>

      <textarea
                id="custom_style_editor"
                name="<?php echo esc_attr($this->option_name); ?>"
                rows="40"
                style="width:100%;"><?php echo esc_textarea($value); ?></textarea>

      <?php submit_button(); ?>
   </form>
</div>
<?php
   }
}
?>
