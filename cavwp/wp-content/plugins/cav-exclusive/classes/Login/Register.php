<?php

namespace cavEx\Login;

class Register
{
   public function __construct()
   {
      add_action('login_enqueue_scripts', [$this, 'change_logo']);

      add_filter('login_headerurl', [$this, 'set_url']);
      add_filter('login_headertext', [$this, 'set_title']);
   }

   public function change_logo()
   {
      switch_to_blog(3);
      $logo = \get_field('logo', 'options');
      $logo = wp_get_attachment_image_url($logo, 'full');
      restore_current_blog();

      ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap"
      rel="stylesheet">
<style type="text/css">
   body.login {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      background: #131314;
      font-family: "JetBrains Mono", monospace;
      font-optical-sizing: auto;
      font-weight: 400;
      font-style: normal;
   }

   button,select,input{
      font-size:inherit!important
   }

   #login {
      padding: 0 !important;
      margin: 0 !important;
   }

   #login h1 a {
      background-image: url(<?php echo $logo; ?>);
      height: 140px;
      width: 320px;
      background-size: 136%;
      background-position: center;
      background-repeat: no-repeat;
   }

   .login form {
      margin: 0 !important;
   }

   .login #backtoblog a,
   .login #nav a {
      color: #fff !important;
   }

   .login form {
      background-color: #DDDDF0 !important;
      border: none!important;
   }

   .language-switcher {
      padding: 0 !important;
      width: 320px;
   }

   #language-switcher {
      font-size: 80%;
   }

   .login form,
   #language-switcher{
      padding: 1.3rem !important;
   }

   .login .message, .login .notice, .login .success{
      background-color: #DDDDF0 !important;
   }

   .login .message{
      border-left-color: #ff5555!important;
   }

   .login .notice{
border-left-color: #8be9fd!important;
   }

   .login .success{
border-left-color: #50fa7b!important;
   }
</style>
<?php
   }

   public function set_title()
   {
      return get_bloginfo('name');
   }

   public function set_url()
   {
      return home_url();
   }
}
?>
