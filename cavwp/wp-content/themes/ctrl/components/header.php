<?php

do_action('get_header');

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
   <?php wp_head(); ?>
</head>

<body id="top" x-data="cav" <?php body_class('select-none text-neutral-100 bg-neutral-800 font-mono text-base overflow-hidden'); ?>>
   <?php wp_body_open(); ?>

   <nav class="absolute top-3 left-3 z-20">
      <ul>
         <li><a class="btn not-focus:sr-only" href="#main">Pular ao conteúdo</a></li>
      </ul>
   </nav>
