<?php

namespace ctrl\Chapter;

class Register
{
   public function __construct()
   {
      add_action('restrict_manage_posts', [$this, 'filter_book_register']);
      add_action('pre_get_posts', [$this, 'filter_book_query']);

      add_filter('manage_chapter_posts_columns', [$this, 'column_book_add']);
      add_action('manage_chapter_posts_custom_column', [$this, 'column_book_fill'], 10, 2);

      add_action('quick_edit_custom_box', [$this, 'quick_edit_book_register'], 10, 2);
      add_action('admin_footer-edit.php', [$this, 'quick_edit_book_js']);
      add_action('save_post_chapter', [$this, 'quick_edit_book_save']);

      add_action('bulk_edit_custom_box', [$this, 'bulk_edit_book_register'], 10, 2);
      add_action('save_post_chapter', [$this, 'bulk_edit_book_save']);
      add_action('admin_footer-edit.php', [$this, 'bulk_edit_book_js']);
   }

   public function bulk_edit_book_js()
   {
      global $post_type;

      if ('chapter' !== $post_type) {
         return;
      }
      ?>
<script>
        jQuery(function($){

            $('#bulk_edit').on('click', function() {

                const book = $('select[name="book_bulk_edit"]').val();

                if (book === '') {
                    return;
                }

                $('<input>').attr({
                    type: 'hidden',
                    name: 'book_bulk_edit_value',
                    value: book
                }).appendTo('#posts-filter');
            });

        });
        </script>
<?php
   }

   public function bulk_edit_book_register($column_name, $post_type)
   {
      if ('chapter' !== $post_type || 'book' !== $column_name) {
         return;
      }

      $books = \wc_get_products([
         'posts_per_page' => -1,
         'orderby'        => 'title',
         'order'          => 'ASC',
         'product_type'   => 'grouped',
      ]);

      ?>
<fieldset class="inline-edit-col-right">
   <div class="inline-edit-col">
      <label>
         <span class="title">Livro</span>
         <select name="book_bulk_edit">
            <option value="">(não alterar)</option>
            <option value="0">(remover)</option>
            <?php foreach ($books as $book) { ?>
            <option value="<?php echo esc_attr($book->get_id()); ?>">
               <?php echo esc_html($book->get_name()); ?>
            </option>
            <?php } ?>
         </select>
      </label>
   </div>
</fieldset>
<?php
   }

   public function bulk_edit_book_save($post_id)
   {
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
         return;
      }

      if (!current_user_can('edit_post', $post_id)) {
         return;
      }

      if (!isset($_REQUEST['book_bulk_edit_value'])) {
         return;
      }

      $book = (int) $_REQUEST['book_bulk_edit_value'];

      if (0 === $book) {
         delete_post_meta($post_id, 'book');

         return;
      }

      update_post_meta($post_id, 'book', $book);
   }

   public function column_book_add($columns)
   {
      $columns['book'] = 'Livro';

      return $columns;
   }

   public function column_book_fill($column, $post_id)
   {
      if ('book' !== $column) {
         return;
      }

      $book_id = get_post_meta($post_id, 'book', true);

      if ($book_id) {
         echo esc_html(get_the_title($book_id));
      }
   }

   public function filter_book_query($query)
   {
      if (!is_admin() || !$query->is_main_query()) {
         return;
      }

      global $pagenow;

      if ('edit.php' !== $pagenow) {
         return;
      }

      if ($query->get('post_type') !== 'chapter') {
         return;
      }

      if (!empty($_GET['book'])) {
         $query->set('meta_query', [
            [
               'key'     => 'book',
               'value'   => (int) $_GET['book'],
               'compare' => '=',
            ],
         ]);
      }
   }

   public function filter_book_register($post_type)
   {
      if ('chapter' !== $post_type) {
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
      echo '<option value="">Todos os livros</option>';

      foreach ($books as $book) {
         printf(
            '<option value="%s" %s>%s</option>',
            $book->get_id(),
            selected($selected, $book->get_id(), false),
            $book->get_name(),
         );
      }

      echo '</select>';
   }

   public function quick_edit_book_js()
   {
      global $post_type;

      if ('chapter' !== $post_type) {
         return;
      }
      ?>
<script>
    jQuery(function($) {

        const $wp_inline_edit = inlineEditPost.edit;

        inlineEditPost.edit = function(id) {
            $wp_inline_edit.apply(this, arguments);

            let postId = 0;

            if (typeof(id) === 'object') {
                postId = parseInt(this.getId(id));
            }

            if (postId > 0) {
                const $row = $('#post-' + postId);
                const bookId = $row.find('.book-id').text();

                const $editRow = $('#edit-' + postId);
                $editRow.find('select[name="book_quick_edit"]').val(bookId);
            }
        };
    });
    </script>
<?php
   }

   public function quick_edit_book_register($column, $post_type)
   {
      if ('chapter' !== $post_type || 'book' !== $column) {
         return;
      }

      $books = wc_get_products([
         'posts_per_page' => -1,
         'orderby'        => 'title',
         'order'          => 'ASC',
         'product_type'   => 'grouped',
      ]);

      ?>
<fieldset class="inline-edit-col-right">
   <div class="inline-edit-col">
      <label>
         <span class="title">Livro</span>
         <select name="book_quick_edit">
            <option value="">(Nenhum)</option>
            <?php foreach ($books as $book) { ?>
            <option value="<?php echo esc_attr($book->get_id()); ?>">
               <?php echo esc_html($book->get_name()); ?>
            </option>
            <?php } ?>
         </select>
      </label>
   </div>
</fieldset>
<?php
   }

   public function quick_edit_book_save($post_id)
   {
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
         return;
      }

      if (!current_user_can('edit_post', $post_id)) {
         return;
      }

      if (isset($_REQUEST['book_quick_edit'])) {
         $book_id = (int) $_REQUEST['book_quick_edit'];

         if ($book_id) {
            update_post_meta($post_id, 'book', $book_id);
         }
      } else {
         delete_post_meta($post_id, 'book');
      }
   }
}
?>
