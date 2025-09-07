<?php

get_component('header');

?>
<main class="max-w-xl mx-auto py-30">
   <h1 class="text-3xl font-semibold uppercase">
      Página não encontrada
   </h1>
   <div <?php post_class('my-12 text-9xl'); ?>>404</div>
   <div>
      <a class="py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold"
         href="<?php echo home_url(); ?>">Início</a>
   </div>
</main>
<?php get_component('footer'); ?>
