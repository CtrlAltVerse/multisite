<?php

get_component('header');

$portfolio = ['tabletop', 'web', 'print'];

?>
<div class="snap-y snap-mandatory overflow-y-auto h-dvh scroll-smooth">
   <header class="relative snap-start flex flex-col items-center justify-center h-dvh">
      <h1 class="sr-only">
         <?php bloginfo('name'); ?>
      </h1>
      <div class="w-11/12 max-w-xl">
         <?php the_custom_logo(); ?>
      </div>
      <div
           class="absolute left-1/2 bottom-9 -translate-x-1/2 flex flex-col items-center justify-center gap-6 w-full max-w-11/12">
         <p
            class="rounded-md py-3 px-6 text-base sm:text-xl font-extrabold uppercase bg-neutral-100 text-neutral-800">
            <?php bloginfo('description'); ?>
         </p>
         <div class="flex items-center text-3xl border-2 rounded-3xl aspect-poster">
            <i class="ri-arrow-down-double-line"></i>
         </div>
      </div>
   </header>
   <?php foreach ($portfolio as $item) {
      get_page_component(__FILE__, 'gallery', [
         'post_type' => $item,
      ]);
   } ?>
   <footer class="relative snap-start flex flex-col items-center justify-center h-dvh">
      <?php if (have_rows('links', 'option')) { ?>
      <div class="grow flex items-center justify-center">
         <ul class="flex gap-3">
            <?php while (have_rows('links', 'option')) {
               the_row(); ?>
            <li class="flex flex-col gap-1">
               <span class="uppercase text-sm sm:text-base">
                  <?php echo get_sub_field('title'); ?>
               </span>
               <?php if (have_rows('group', 'option')) { ?>
               <ul
                   class="flex flex-col sm:flex-row rounded border border-neutral-100 divide-y sm:divide-y-0 sm:divide-x">
                  <?php while (have_rows('group', 'option')) {
                     the_row(); ?>
                  <li>
                     <a class="link"
                        href="<?php echo get_sub_field('link'); ?>"
                        target="_blank" rel="external nofollow">
                        <i
                           class="<?php echo get_sub_field('icon'); ?>"></i>
                        <?php echo get_sub_field('label'); ?>
                     </a>
                  </li>
                  <?php } ?>
               </ul>
               <?php } ?>
            </li>
            <?php } ?>
         </ul>
      </div>
      <?php } ?>
      <div class="max-w-xs mb-6">
         <?php the_custom_logo(); ?>
      </div>
   </footer>
</div>
<?php get_component('footer'); ?>
