<?php

get_component('header');

the_post();

?>
<header class="container-post flex flex-col gap-3 pt-10 font-mono text-base">
   <nav class="flex flex-col gap-1.5 w-full">
      <ul class="flex items-center gap-1.5 font-medium">
         <li>
            <a href="<?php echo home_url(); ?>">Início</a>
         </li>
         <li>
            &rsaquo;
         </li>
         <li>
            <a
               href="<?php echo home_url('artigos'); ?>">Publicações</a>
         </li>
      </ul>
   </nav>
   <h1 class="text-2xl sm:text-4xl font-semibold">
      <?php the_title(); ?>
   </h1>
   <div class="text-xl sm-text-2xl">
      <?php the_excerpt(); ?>
   </div>
   <div class="font-medium">
      <?php the_author(); ?>
   </div>
</header>

<main <?php post_class('container-post pt-10 pb-25 font-serif text-lg'); ?>>
   <?php if (has_post_thumbnail()) { ?>
   <div class="flex flex-col gap-1.5 mx-auto pb-4 w-175 font-sans">
      <?php the_post_thumbnail('large', [
         'class' => 'rounded',
      ]); ?>
      <div class="text-base">
         <?php the_post_thumbnail_caption(); ?>
      </div>
   </div>
   <?php } ?>
   <?php the_content(); ?>
   <?php the_category('', ''); ?>
</main>
<footer class="container mt-20 font-mono text-lg">
   <section id="list">
      <?php

$others = get_posts([
   'post_status' => 'publish',
   'nopaging'    => true,
   'order'       => 'ASC',
   'orderby'     => 'title',
]);

$others     = array_values(array_filter($others, fn($other) => get_the_ID() !== $other->ID));
$per_column = ceil(count($others) / 4);
$columns    = range(0, 3);

?>
      <h2 class="text-xl font-semibold mb-4">Outras Publicações</h2>
      <nav class="flex flex-wrap gap-var-1.5 ">
         <?php foreach ($columns as $column) { ?>
         <ul class="shrink-0 flex flex-col gap-1.5 lg:cols-2 xl:cols-4">
            <?php for ($i = $per_column * $column; $i < ($per_column * $column) + $per_column; $i++) { ?>
            <?php $other = $others[$i]; ?>
            <?php if (empty($other)) { ?>
            <?php continue; ?>
            <?php } ?>
            <li>
               <a class="line-clamp-2"
                  href="<?php echo get_permalink($other->ID); ?>">
                  <?php echo $other->post_title; ?>
               </a>
            </li>
            <?php } ?>
         </ul>
         <?php } ?>
      </nav>

   </section>
   <div class="text-neutral-100">
      <?php get_component('footer-logo', [
         'home_url' => 'artigos',
      ]); ?>
   </div>
</footer>
<?php get_component('footer'); ?>
