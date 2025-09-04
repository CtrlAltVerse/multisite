<?php

$post_types = get_post_types(['public' => true, 'rest_namespace' => 'random/v1'], 'objects');

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php bloginfo('name'); ?></title>
   <meta name="description"
         content="<?php bloginfo('description'); ?>">
</head>
<body>
   <main>
      <hgroup>
         <h1><?php bloginfo('name'); ?></h1>
         <p><?php bloginfo('description'); ?></p>
      </hgroup>
      <section>
         <h2>Random</h2>
         <?php foreach ($post_types as $post_type) { ?>
         <?php $url = get_rest_url(null, $post_type->rest_namespace . '/' . $post_type->name); ?>
         <article>
            <hgroup>
               <h3><?php echo $post_type->label; ?></h3>
               <p><?php echo $post_type->description; ?></p>
            </hgroup>
            <a href="<?php echo $url; ?>"><?php echo $url; ?></a>
         </article>
         <?php } ?>
      </section>
   </main>
</body>
</html>
