<?php

use cavWP\Models\Post;

$post_type = $args['post_type'];

$all_posts = get_posts([
   'post_type'     => $post_type,
   'post_per_page' => -1,
]);

if (empty($all_posts)) {
   return;
}

$post_type_object = get_post_type_object($post_type);

?>
<div class="relative snap-start overflow-hidden h-screen">
   <section id="<?php echo $post_type; ?>">
      <h2 class="sr-only absolute z-5 top-6 left-6 text-base uppercase">
         <?php echo $post_type_object->label; ?>
      </h2>
      <div id="<?php echo $post_type; ?>-list"
           class="absolute top-0 left-0 flex items-end h-screen w-min transition-[left]">
         <?php foreach ($all_posts as $item) { ?>
         <?php $Post = new Post($item); ?>
         <?php $Bg   = new Post($Post->get_meta('background'));
            ?>
         <article id="<?php echo $Post->get('slug'); ?>"
                  class="relative w-screen h-screen">
            <div class="relative z-5 flex flex-col gap-7 py-8 px-6">
               <hgroup class="flex flex-col gap-2">
                  <h3 class="text-xl sm:text-3xl font-semibold uppercase">
                     <?php echo $Post->get('title'); ?>
                  </h3>
                  <p class="text-lg sm:text-2xl font-medium">
                     <?php echo $Post->get('author:name'); ?>
                  </p>
               </hgroup>
               <p class="line-clamp-6 text-base font-medium max-w-xl">
                  <?php echo $Post->get('summary', apply_filter: false); ?>
               </p>
               <?php if (have_rows('links', $item->ID)) { ?>
               <ul class="flex gap-3 w-full overflow-x-auto">
                  <?php while (have_rows('links', $item->ID)) {
                     the_row(); ?>
                  <li class="flex flex-col gap-1">
                     <span class="uppercase text-base">
                        <?php echo get_sub_field('title'); ?>
                     </span>
                     <?php if (have_rows('group', $item->ID)) { ?>
                     <ul class="flex rounded border border-neutral-100 divide-x">
                        <?php while (have_rows('group', $item->ID)) {
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
               <div class="text-sm">Foto:
                  <?php echo $Bg->get('summary', apply_filter: false); ?>
               </div>
            </div>
            <div class="absolute inset-0 z-1 w-screen h-screen bg-neutral-800/65"></div>
            <?php echo $Bg->get('thumb', size: 'full', with_html: true, attrs: [
               'class' => 'absolute inset-0 z-0 w-screen h-screen object-cover object-center',
            ]); ?>
         </article>
         <?php } ?>
      </div>
      <ul class="absolute left-6 right-6 bottom-5 z-4 flex gap-2 w-min h-45 sm:h-60 overflow-x-auto">
         <?php foreach ($all_posts as $key => $item) { ?>
         <?php $Post = new Post($item); ?>
         <li
             class="h-11/12 w-auto <?php echo 'print' === $post_type ? 'aspect-poster' : 'aspect-video'; ?>">
            <button class="cursor-pointer"
                    title="<?php echo $Post->get('title'); ?>"
                    type="button"
                    x-on:click="<?php echo $post_type; ?>=<?php echo $key; ?>">
               <?php echo $Post->get('thumb', with_html: true, attrs: [
                  'class' => 'rounded object-cover size-full',
               ]); ?>
            </button>
         </li>
         <?php } ?>
      </ul>
   </section>
</div>
