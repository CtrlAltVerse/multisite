<?php

$post_ID = get_the_ID();
$link    = get_post_meta($post_ID, 'link', true);

if (!empty($link)) {
   $views = (int) get_post_meta($post_ID, 'views', true);
   $views++;
   update_post_meta($post_ID, 'views', $views);

   if (wp_redirect($link)) {
      exit;
   }
}

if (!have_rows('links')) {
   wp_die('Nenhum link cadastrado.');
}

get_component('header');

?>
<header>
   <h1><?php the_title(); ?></h1>
</header>
<main>
   <ul class="list">
      <?php
   while (have_rows('links')) {
      the_row();

      $bg = '';

      $color = get_sub_field('bg');

      if (!empty($color)) {
         $bg = 'background-color:' . $color;
      }

      ?>
      <li class="item">
         <a class="link <?php echo (empty($bg)) ? 'with-border' : ''; ?>"
            href="<?php echo get_sub_field('url'); ?>"
            style="<?php echo $bg; ?>"
            target="_blank">
            <?php echo get_sub_field('svg'); ?>

            <span class="uppercase text-sm sm:text-base">
               <?php echo get_sub_field('label'); ?>
            </span>
         </a>
         <div class="box"></div>
      </li>
      <?php } ?>
   </ul>
</main>
<?php

get_component('footer');

?>
