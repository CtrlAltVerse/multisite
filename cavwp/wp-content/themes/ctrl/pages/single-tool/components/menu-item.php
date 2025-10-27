<li class="flex justify-between group">
   <a class="py-1 grow" x-bind:href="tool.link"
      x-bind:title="tool.description" x-text="tool.title">
   </a>
   <button class="pl-2.5 hidden group-hover:inline"
      x-on:click.prevent="toggleFavorite(tool.ID)">
      <i class="ri-star-fill"x-show="!favoritesIds.includes(tool.ID)"></i>
      <i class="ri-star-off-line" x-show="favoritesIds.includes(tool.ID)"></i>
   </button>
</li>
