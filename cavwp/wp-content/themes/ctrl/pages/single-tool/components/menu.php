<dialog id="tools-menu"
        class="ml-auto w-full max-w-xs max-h-dvh h-full text-neutral-100 bg-neutral-700 backdrop:bg-neutral-900/60"
        x-on:click.stop.self="toggleToolsMenu">
   <nav
        class="py-4 px-5 border-l border-neutral-500 min-h-dvh"
        aria-label="Todas as ferramentas" x-on:click.stop.outside="toggleToolsMenu">
      <div class="flex items-center justify-between -mt-4 -mx-5 mb-4 py-4 px-5 bg-neutral-800">
         <span class="font-semibold text-lg flex gap-2 items-center">
            Todas as ferramentas
         </span>
         <button class="pl-2.5" x-on:click.prevent="toggleToolsMenu">
            <i class="ri-close-large-fill"></i>
         </button>
      </div>
      <div x-show="favorites.length" x-cloak>
         <div class="font-semibold text-lg flex items-center gap-2 mb-3">
            <i class="ri-star-fill"></i>
            Favoritos
         </div>
         <ul class="flex gap-2 flex-col py-2">
            <template x-for="tool in favorites">
               <?php get_page_component('single-tool', 'menu-item'); ?>
            </template>
         </ul>
         <hr class="my-4 border-neutral-500">
      </div>
      <ul class="flex gap-2 flex-col py-2" x-show="tools.length" x-cloak>
         <template x-for="tool in tools">
            <?php get_page_component('single-tool', 'menu-item'); ?>
         </template>
      </ul>
   </nav>
</dialog>
