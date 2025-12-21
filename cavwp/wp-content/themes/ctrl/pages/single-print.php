<?php

use cavEx\Rewards\Utils as RewardsUtils;
use ctrl\Utils;

get_component('header');

the_post();
global $post;

$bg        = get_field('background');
$part_type = get_field('part_type');

$content = get_extended($post->post_content);
$unlocks = get_user_meta(get_current_user_id(), 'unlocks', true);

$extras = [
   [
      'label' => get_the_title(),
      'value' => get_the_author_meta('display_name'),
   ],
   [
      'label' => 'Lançamento',
      'value' => get_the_date('Y'),
   ],
];

$rewards     = RewardsUtils::get_rewards_by(get_the_ID());
$is_unlocked = RewardsUtils::is_unlocked(get_the_ID(), 'digital');
$chapters    = Utils::get_chapters(get_the_ID());

?>
<header class="relative py-15 font-mono text-neutral-100">
   <?php if (!empty($bg)) { ?>
   <div class="absolute inset-0 z-1 bg-neutral-900/65"></div>
   <img class="absolute inset-0 z-0 size-full object-cover"
        src="<?php echo wp_get_attachment_image_url($bg, 'full'); ?>"
        alt="" />
   <?php } ?>
   <div class="container max-w-4xl relative z-5 flex flex-col gap-1.5 w-full">
      <ul class="flex items-center gap-1.5 font-medium">
         <li>
            <a href="<?php echo home_url(); ?>">Início</a>
         </li>
         <li>
            &rsaquo;
         </li>
         <li>
            <a
               href="<?php echo home_url('#print-list'); ?>">
               Impresso
            </a>
         </li>
      </ul>
      <h1 class="text-2xl sm:text-3xl font-semibold uppercase">
         <?php the_title(); ?>
         <span class="text-neutral-500 normal-case">de
            <?php echo get_the_author_meta('display_name'); ?></span>
      </h1>
   </div>
</header>
<main class="container max-w-4xl my-25 flex flex-col md:flex-row gap-12 font-serif">
   <div <?php post_class('grow'); ?>>
      <div class="text-2xl">
         <?php the_excerpt(); ?>
      </div>
      <hr />
      <?php if (!empty($content['extended'])) { ?>
      <?php echo $content['main']; ?>
      <hr />
      <h2>
         <?php the_title(); ?><br><?php echo get_the_author_meta('display_name'); ?>
      </h2>
      <?php echo apply_filters('the_content', $content['extended']); ?>
      <?php } else { ?>
      <?php echo apply_filters('the_content', $content['main']); ?>
      <?php } ?>
      <?php if (!empty($chapters) && $is_unlocked) { ?>
      <h2>Sumário</h2>
      <ol>
         <?php foreach ($chapters as $chapter) { ?>
         <li>
            <a href="<?php echo get_the_permalink($chapter->ID); ?>"
               title="Ler <?php echo $part_type; ?>">
               <?php echo get_the_title($chapter); ?>
            </a>
         </li>
         <?php } ?>
      </ol>
      <?php } ?>
   </div>
   <div class="flex-0 w-sm mx-auto font-mono">
      <?php if (has_post_thumbnail()) { ?>
      <?php the_post_thumbnail('thumbnail', [
         'class' => 'w-full rounded-sm',
      ]); ?>
      <?php } ?>
      <ul class="flex flex-col gap-6 mt-6">
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
      <?php if (have_rows('links')) { ?>
      <ul class="flex flex-col gap-6 mt-6 ">
         <?php while (have_rows('links')) {
            the_row(); ?>
         <li class="flex flex-col gap-1">
            <span class="uppercase text-sm sm:text-base">
               <?php echo get_sub_field('title'); ?>
            </span>
            <?php if (have_rows('group')) { ?>
            <ul class="flex flex-col rounded border border-neutral-700 divide-y">
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
      <?php if (!empty($rewards)) { ?>
      <ul class="flex flex-col gap-6 mt-6" x-data="rewards">
         <?php foreach ($rewards as $reward) { ?>
         <li class="flex flex-col gap-1">
            <span class="uppercase text-sm sm:text-base">
               <?php echo $reward->type_label; ?>
            </span>
            <ul class="flex flex-col rounded border border-neutral-700 divide-y text-base">
               <?php foreach ($reward->prices as $price) { ?>
               <li>
                  <button class="link !flex items-center justify-between gap-6" type="button"
                          x-on:click.prevent="redeemReward(<?php echo $reward->reward_ID; ?>)">
                     <span>
                        <?php echo $price['label']; ?>
                     </span>
                     <span class="flex flex-col items-end text-sm">
                        <?php echo $price['value']; ?>
                     </span>
                  </button>
               </li>
               <?php } ?>
            </ul>
         </li>
         <?php } ?>
      </ul>
      <div class="text-sm text-center mt-1"><a
            href="<?php echo home_url('ganhando-xp'); ?>"
            target="_blank">
            <i class="ri-information-2-line"></i>
            Como conseguir XP
         </a></div>
      <?php } ?>
   </div>
</main>
<footer class="text-neutral-800">
   <?php get_component('footer-logo', [
      'home_url' => '#print-list',
   ]); ?>
</footer>
<?php get_component('footer'); ?>
