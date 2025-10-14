<?php

use ctrl\Utils;

get_component('header');

the_post();
global $post;

$book      = get_field('book');
$bg        = get_field('background', $book);
$part_type = get_field('part_type', $book);

$chapters = Utils::get_chapters($book);

$past = false;
$prev = false;
$next = false;

foreach ($chapters as $chapter) {
   if (!empty($next)) {
      break;
   }

   if ($chapter->menu_order === $post->menu_order) {
      $past = true;
      continue;
   }

   if (false === $past) {
      $prev = $chapter;
      continue;
   }

   if (true === $past) {
      $next = $chapter;
      continue;
   }
}

?>
<header class="relative py-15 font-mono text-base text-neutral-100">
   <?php if (!empty($bg)) { ?>
   <div class="absolute inset-0 z-1 bg-neutral-900/65"></div>
   <img class="absolute inset-0 z-0 size-full object-cover"
        src="<?php echo wp_get_attachment_image_url($bg, 'full'); ?>"
        alt="" />
   <?php } ?>
   <div class="container-chapter relative z-5 flex flex-col gap-1.5 w-full">
      <ul class="flex items-center gap-1.5 font-medium">
         <li>
            <a
               href="<?php echo home_url(); ?>">Início</a>
         </li>
         <li>
            &rsaquo;
         </li>
         <li>
            <a
               href="<?php echo home_url('#print-list'); ?>">Impressos</a>
         </li>
         <li>
            &rsaquo;
         </li>
         <li>
            <a href="<?php echo get_permalink($book); ?>">
               <?php echo get_the_title($book); ?>
            </a>
         </li>
      </ul>
      <div class="text-2xl sm:text-3xl font-semibold uppercase">
         <?php the_title(); ?>
      </div>
   </div>
</header>
<main <?php post_class('container-chapter py-25 font-serif'); ?>>
   <h1 class="text-2xl sm:text-4xl font-semibold mb-30 ">
         <?php the_title(); ?>
      </h1>

   <?php the_content(); ?>
</main>
<footer class="font-mono text-lg">
   <a class="container-chapter relative flex flex-col gap-1 mb-15 border border-neutral-500 rounded py-4 px-5 min-h-50 bg-neutral-900/9"
      href="<?php echo get_permalink($book); ?>">
      <div class="pr-28 sm:pr-40">
         <h2 class="mb-3 text-sm uppercase">Parte da publicação</h2>
         <strong
                 class="text-xl"><?php echo get_the_title($book); ?></strong>
         <p>
            <?php echo get_the_author_meta('display_name', get_post_field('post_author', $post_id)); ?>
         </p>
         <p class="mt-3 font-sm">Contém
            <?php echo count($chapters); ?>
            <?php echo $part_type; ?>s
         </p>
      </div>
      <img class="absolute top-3 sm:-top-5 right-5 rounded aspect-poster object-cover h-44 sm:h-60 shadow-md pointer-events-none"
           src="<?php echo get_the_post_thumbnail_url($book, 'thumbnail'); ?>"
           alt="" />
   </a>
   <div class="container-chapter grid grid-rows-2 sm:grid-cols-2 gap-3">
      <div class="order-2 sm:order-none">
         <?php if (!empty($prev)) { ?>
         <a class="flex justify-start items-center gap-1 border border-neutral-500 rounded py-3 px-3"
            href="<?php echo get_permalink($prev->ID); ?>">
            <i class="ri-arrow-left-s-line text-xl"></i>
            <span class="flex flex-col">
               <span
                     class="text-sm uppercase"><?php echo $part_type; ?>
                  anterior</span>
               <span
                     class="text-lg font-medium tracking-tighter"><?php echo get_the_title($prev); ?></span>
            </span>
         </a>
         <?php } ?>
      </div>
      <div class="order-1 sm:order-none">
         <?php if (!empty($next)) { ?>
         <a class="flex justify-end items-center gap-1 border border-neutral-500 rounded p-3 text-right"
            href="<?php echo get_permalink($next->ID); ?>">
            <span class="flex flex-col">
               <span class="text-sm uppercase">Próximo
                  <?php echo $part_type; ?></span>
               <span
                     class="text-lg font-medium tracking-tighter"><?php echo get_the_title($next); ?></span>
            </span>
            <i class="ri-arrow-right-s-line text-xl"></i>
         </a>
         <?php } ?>
      </div>
   </div>
   <div class="text-neutral-700">
      <?php get_component('footer-logo'); ?>
   </div>
</footer>
<?php get_component('footer'); ?>
