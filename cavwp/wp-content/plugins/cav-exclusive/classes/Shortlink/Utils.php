<?php

namespace cavEx\Shortlink;

class Utils
{
   public static function create_shortlink($title, $link)
   {
      if (is_multisite()) {
         switch_to_blog(1);
      }

      $post_ID = wp_insert_post([
         'post_title'     => $title,
         'post_status'    => 'publish',
         'post_type'      => 'shortlink',
         'comment_status' => 'closed',
         'ping_status'    => 'closed',
         'meta_input'     => [
            'link' => $link,
         ],
      ]);

      if (is_multisite()) {
         restore_current_blog();
      }

      return $post_ID;
   }

   public static function get_link($link_ID)
   {
      if (is_multisite()) {
         switch_to_blog(1);
      }

      $post_obj = get_post($link_ID);

      if (is_null($post_obj)) {
         return false;
      }

      $uploads = wp_upload_dir()['basedir'] . '/';
      $qr_code = $uploads . 'qrcode-' . $link_ID . '.png';

      $return = [
         'link'    => home_url($post_obj->post_name),
         'qr_code' => $qr_code,
      ];

      if (is_multisite()) {
         restore_current_blog();
      }

      return $return;
   }

   public static function update_shortlink($link_ID, $link)
   {
      if (is_multisite()) {
         switch_to_blog(1);
      }

      $updated = update_post_meta($link_ID, 'link', $link);

      if (is_multisite()) {
         restore_current_blog();
      }

      return $updated;
   }
}
