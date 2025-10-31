<?php

get_component('header');

?>
<div x-data="tools" class="text-base min-w-xs">
   <div class="container mx-auto flex flex-col min-h-dvh">
      <header class="flex gap-2 justify-between items-start px-4 py-3">
         <hgroup>
            <h1>Ferramentas</h1>
            <p></p>
         </hgroup>
         <div class="flex gap-3 items-center">
            <button class="text-2xl" title="Todas as ferramentas" x-on:click.prevent="toggleToolsMenu" type="button">
               <i class="ri-menu-fill"></i>
            </button>
         </div>
         <?php get_page_component('single-tool', 'menu'); ?>
      </header>
      <div class="grow">
         <main class="my-6 px-3.5">
            <div x-show="favorites.length" x-cloak>
               <ul class="grid xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-3">
                  <template x-for="tool in favorites">
                     <li>
                        <a class="block rounded py-3 px-5 size-full bg-neutral-300/30 dark:bg-neutral-500/30" x-bind:href="tool.link">
                           <h2 class="line-clamp-2 font-semibold text-lg mb-2" x-text="tool.title"></h2>
                           <p class="line-clamp-4" x-text="tool.description"></p>
                        </a>
                     </li>
                  </template>
               </ul>
               <hr class="my-4">
            </div>
            <ul class="grid xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-3">
               <template x-for="tool in tools">
                  <li>
                     <a class="block rounded py-3 px-5 size-full bg-neutral-300/30 dark:bg-neutral-500/30" x-bind:href="tool.link">
                        <h2 class="line-clamp-2 font-semibold text-lg mb-2" x-text="tool.title"></h2>
                        <p class="line-clamp-4" x-text="tool.description"></p>
                     </a>
                  </li>
               </template>
            </ul>
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
