<?php

use cavWP\Utils;

get_component('header');

$nav_items = Utils::paginate_links(
   [
      'mid_size'  => 3,
      'next_text' => 'Próxima página',
      'prev_text' => 'Página anterior',
   ],
   [
      'page-numbers current' => 'link !text-sm bg-neutral-100 text-neutral-800',
      'page-numbers'         => 'link !text-sm',
   ],
);

?>
<div class="container mx-auto my-4">
   <div class="flex flex-col min-w-xs max-w-xl min-h-dvh">
      <header class="mt-12 mb-22">
         <hgroup>
            <h1 class="text-2xl font-semibold">Artigos</h1>
            <p>Tutoriais, showcases e nosso método de desenvolvimento em profundidade.</p>
         </hgroup>
      </header>
      <main>
         <h2 class="sr-only">Publicações recentes</h2>
         <?php if (have_posts()) { ?>
         <ul class="flex flex-col gap-12">
            <?php

            while (have_posts()) {
               the_post();

               ?>
            <li>
               <h3 class="text-xl font-semibold mb-1">
                  <a
                     href="<?php echo get_permalink(); ?>">
                     <?php the_title(); ?>
                  </a>

               </h3>
               <div class="text-base">
                  <a
                     href="<?php echo get_permalink(); ?>">
                     <?php the_excerpt(); ?>
                  </a>
               </div>

            </li>
            <?php } ?>
         </ul>
         <?php } ?>
      </main>
      <nav class="mt-12">
               <ul class="flex justify-center gap-px sm:gap-2 *:flex">
                  <?php foreach ($nav_items as $nav_item) { ?>
                  <li class="rounded border border-neutral-100">
                     <?php echo $nav_item; ?>
                  </li>
                  <?php } ?>
               </ul>
      </nav>
   </div>
</div>
<footer>
   <?php get_component('footer-logo'); ?>
</footer>
<?php

get_component('footer');

?>
