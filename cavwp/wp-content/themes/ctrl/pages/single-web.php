<?php

use cavEx\services\GitHub;

get_component('header');

the_post();
global $post;

$bg   = get_field('background');
$repo = get_field('repo');

if (!empty($repo)) {
   $GitHub  = new GitHub();
   $commits = $GitHub->get_commits($repo);

   if (is_array($commits)) {
      $commits = array_filter($commits, fn($commit) => strlen($commit['commit']['message']) > 7);
   }
}

$content = get_extended($post->post_content);

?>
<header class="relative py-15 font-mono text-neutral-100">
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
               href="<?php echo home_url('#web-list'); ?>>">
               Web
            </a>
         </li>
      </ul>
      <h1 class="text-2xl sm:text-3xl font-semibold uppercase">
         <?php the_title(); ?>
      </h1>
   </div>
</header>
<main class="container my-25 flex flex-col md:flex-row gap-12">
   <div <?php post_class('grow'); ?>>
      <?php if (!empty($content['extended'])) { ?>
      <div class="text-2xl">
         <?php echo $content['main']; ?>
      </div>
      <?php echo $content['extended']; ?>
      <?php } else { ?>
      <?php echo $content['main']; ?>
      <?php } ?>
      <?php if (!empty($commits)) { ?>
      <h2>Últimas atualizações</h2>
      <ul class="!list-none">
         <?php foreach ($commits as $commit) { ?>
         <li>
            <a class="flex items-center gap-2"
               href="<?php echo $commit['html_url']; ?>"
               target="_blank" rel="external">
               <img class="size-8 rounded-full"
                    src="<?php echo $commit['author']['avatar_url']; ?>"
                    alt="" />
               <?php echo $commit['commit']['message']; ?>
               <span class="text-neutral-500">
                  há
                  <?php echo human_time_diff(strtotime($commit['commit']['committer']['date'])); ?>
               </span>
            </a>
         </li>
         <?php } ?>
      </ul>
      <p>
         <a
            href="https://github.com/<?php echo $repo; ?>"
            target="_blank" rel="external">Projeto no <i class="ri-github-fill"></i> GitHub</a>.
         <a href="https://gitmoji.dev/" target="_blank" rel="external">Para significado dos emojis, veja gitmoji</a>.
      </p>
      <?php } ?>
   </div>
   <div class="shrink-0 max-w-sm mx-auto">
      <?php if (has_post_thumbnail()) { ?>
      <?php the_post_thumbnail('thumbnail', [
         'class' => 'w-full rounded-sm',
      ]); ?>
      <?php } ?>
      <div class="mt-3">
         <?php the_excerpt(); ?>
      </div>
      <?php if (have_rows('links')) { ?>
      <ul class="flex flex-col gap-6 mt-6">
         <?php while (have_rows('links')) {
            the_row(); ?>
         <li class="flex flex-col gap-1">
            <span class="uppercase text-sm sm:text-base">
               <?php echo get_sub_field('title'); ?>
            </span>
            <?php if (have_rows('group')) { ?>
            <ul class="flex flex-col rounded border border-neutral-100 divide-y">
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
</main>
<footer>
   <?php get_component('footer-logo'); ?>
</footer>
<?php get_component('footer'); ?>
