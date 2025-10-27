<?php

get_component('header');

the_post();

global $post;

$post_slug = $post->post_name;

?>
<div x-data="tools" class=" text-base min-w-xs">
   <div class="container mx-auto flex flex-col min-h-dvh">
      <header class="flex gap-2 justify-between items-center px-4 py-3">
         <hgroup>
            <h1><?php the_title(); ?></h1>
            <?php the_excerpt(); ?>
         </hgroup>
         <div class="flex gap-3 items-center">
            <button class="text-2xl" title="Todas as ferramentas" x-on:click.prevent="toggleToolsMenu" type="button">
               <i class="ri-menu-fill"></i>
            </button>
         </div>
         <?php get_page_component(__FILE__, 'menu'); ?>
      </header>
      <div class="grow">
         <main class="my-6 px-3.5">
            <?php get_page_component(__FILE__, 'tools/' . $post_slug); ?>
         </main>
      </div>
      <footer class="text-sm text-center my-4">
            <a href="<?php bloginfo('url'); ?>">
               <?php bloginfo('name'); ?>
            </a>
      </footer>
   </div>
</div>
<?php

get_component('footer');

?>
