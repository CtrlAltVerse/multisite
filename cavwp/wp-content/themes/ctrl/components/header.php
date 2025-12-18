<?php

do_action('get_header');

$body_classes = '';

if (is_home() || is_page() || is_singular(['web'])) {
   $body_classes .= ' bg-space text-neutral-100 font-mono text-base';
}

if (is_home()) {
   $body_classes .= ' select-none overflow-hidden';
}

if (is_singular(['print', 'chapter'])) {
   $body_classes .= ' bg-neutral-200 text-neutral-800 select-none';
}

if (is_singular('tool') || is_post_type_archive('tool') || is_singular(['post'])) {
   $body_classes .= ' bg-neutral-200 text-neutral-800 dark:text-neutral-200 dark:bg-space font-mono text-base';
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
   <?php wp_head(); ?>
</head>

<body id="top" x-data="cav" <?php body_class($body_classes); ?>>
   <?php wp_body_open(); ?>

   <nav class="absolute top-3 left-3 z-20">
      <ul>
         <li><a class="btn not-focus:sr-only" href="#main">Pular ao conteúdo</a></li>
      </ul>
   </nav>
