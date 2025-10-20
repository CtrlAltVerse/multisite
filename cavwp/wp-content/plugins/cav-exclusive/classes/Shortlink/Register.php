<?php

namespace cavEx\Shortlink;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Register
{
   public function __construct()
   {
      add_action('save_post_shortlink', [$this, 'update_slug'], 5, 2);
      add_action('save_post_shortlink', [$this, 'update_qrcode'], 55, 2);
   }

   public function update_qrcode($post_ID, $post_obj)
   {
      if ('publish' !== $post_obj->post_status) {
         return;
      }

      $already = (int) get_post_meta($post_ID, 'has_qrcode', true);

      if (!empty($already)) {
         return;
      }

      update_post_meta($post_ID, 'has_qrcode', 1);

      $this->create_qrcode($post_ID, get_permalink($post_ID));
   }

   public function update_slug($post_ID, $post_obj)
   {
      if ('publish' !== $post_obj->post_status) {
         return;
      }

      $already = (int) get_post_meta($post_ID, 'has_code', true);

      if (!empty($already)) {
         return;
      }

      update_post_meta($post_ID, 'has_code', 1);

      wp_update_post([
         'ID'        => $post_ID,
         'post_name' => $this->create_code(),
      ]);
   }

   private function create_code($length = 3)
   {
      $chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXYZ0123456789-_';

      $code = '';

      for ($i = 0; $i < $length; $i++) {
         $code .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
      }

      return $code;
   }

   private function create_qrcode($post_ID, $data)
   {
      require_once ABSPATH . 'vendor/autoload.php';

      $uploads = wp_upload_dir()['basedir'] . '/';

      $options                = new QROptions();
      $options->version       = 3;
      $options->outputType    = 'png';
      $options->quietzoneSize = 1;

      $qrcode = new QRCode($options);
      $qrcode->render($data, $uploads . 'qrcode-' . $post_ID . '.png');
   }
}
