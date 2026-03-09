<?php

use ctrl\Hector\Utils as BookUtils;

get_component('header');

$unlocks = get_user_meta(get_current_user_id(), 'unlocks', true);

global $post;
$product = wc_get_product($post);

$category = get_the_terms($post, 'product_cat')[0];
$bg       = get_field('background');
$extras   = [
   [
      'label' => get_the_title(),
      'value' => BookUtils::get_author_names($post->ID),
   ],
   [
      'label' => 'Lançamento',
      'value' => get_field('release', $post->ID),
   ],
];

$attributes = $product->get_attributes();

foreach ($attributes as $attribute) {
   $extras[] = [
      'label' => $attribute->get_taxonomy_object()->attribute_label,
      'value' => $attribute->get_terms()[0]->name,
   ];
}

$tags = wc_get_product_tag_list($post->ID);

$extras[] = [
   'label' => 'Gêneros',
   'value' => $tags,
];

the_post();

?>
<header class="relative py-12 font-mono text-neutral-100 overflow-hidden">
   <?php if (!empty($bg)) { ?>
   <div class="absolute inset-0 z-1 bg-neutral-900/65"></div>
   <img class="absolute inset-0 z-0 size-full object-cover"
        src="<?php echo wp_get_attachment_image_url($bg, 'full'); ?>"
        alt="" />
   <?php } ?>
   <div class="container relative z-5 flex flex-col gap-1.5 w-full">
      <ul class="flex items-center gap-1.5 font-medium">
         <li>
            <a href="<?php echo home_url(); ?>">Início</a>
         </li>
         <li>
            &rsaquo;
         </li>
         <li>
            <a
               href="<?php echo home_url('#' . $category->slug . '-list'); ?>">
               Impresso
            </a>
         </li>
      </ul>
      <h1 class="text-2xl sm:text-3xl font-semibold uppercase">
         <?php the_title(); ?>
         <span class="text-neutral-500 normal-case">de
            <?php echo BookUtils::get_author_names($post->ID); ?></span>
      </h1>
   </div>
</header>
<main <?php post_class('container relative my-20 font-serif'); ?>>
   <?php get_page_component(__FILE__, 'images'); ?>
   <div class="absolute z-5 top-2 left-0 max-w-md">
      <div class="text-lg mb-10">
         <?php the_excerpt(); ?>
      </div>
      <ul class="flex flex-col gap-6">
         <?php foreach ($extras as $extra) { ?>
         <li class="flex flex-col gap-1">
            <span class="uppercase">
               <?php echo $extra['label']; ?>
            </span>
            <span class="font-semibold">
               <?php echo $extra['value']; ?>
            </span>
         </li>
         <?php } ?>
      </ul>
   </div>
   <div class="absolute z-5 top-2 right-0">
      <section>
         <h2 class="sr-only">Compra disponível nos seguintes formatos ou lojas</h2>
         <?php get_page_component(__FILE__, $product->get_type() . '-prices'); ?>
      </section>

      <?php if (have_rows('links')) { ?>
      <ul class="flex flex-col sm:flex-row flex-wrap gap-6 mt-6">
         <?php while (have_rows('links')) {
            the_row(); ?>
         <li class="flex flex-col gap-1">
            <span class="uppercase text-sm sm:text-base">
               <?php echo get_sub_field('title'); ?>
            </span>
            <?php if (have_rows('group')) { ?>
            <ul class="flex flex-col sm:flex-row rounded border border-neutral-700 divide-y sm:divide-x sm:divide-y-0">
               <?php while (have_rows('group')) {
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
      <?php } ?>
   </div>
   <section class="max-w-5xl mx-auto mt-10 hentry">
      <?php echo $product->get_description(); ?>
   </section>

</main>
<footer class="text-neutral-800">
   <?php get_component('footer-logo', [
      'home_url' => '#' . $category->slug . '-list',
   ]); ?>
</footer>
<?php get_component('footer'); ?>
