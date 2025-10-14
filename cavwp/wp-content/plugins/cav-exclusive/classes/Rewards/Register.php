<?php

namespace cavEx\Rewards;

final class Register
{
   public function __construct()
   {
      new Register_Endpoint();

      add_action('wp_enqueue_scripts', [$this, 'register_assets']);
   }

   public function register_assets()
   {
      wp_register_script('rewards', plugin_dir_url(CAV_EX_FILE) . 'assets/rewards.min.js', [], '1.0', [
         'strategy' => 'defer',
      ]);

      wp_localize_script('rewards', 'cavRewards', [
         'endpoint' => rest_url('/cav/v1/rewards'),
         'nonce'    => wp_create_nonce('wp_rest'),
      ]);
   }
}
