<?php

get_component('header');

?>
<main class="max-w-xl mx-auto pt-30">
   <h1 class="text-2xl font-semibold">
      <?php the_title(); ?>
   </h1>
   <div <?php post_class('my-12 *:mb-6'); ?>>
      <?php the_content(); ?>
   </div>
   <div>
   <a class="py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold"
      href="<?php echo home_url(); ?>">Home</a>
      </div>
</main>
<?php get_component('footer'); ?>
