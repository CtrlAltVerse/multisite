<?php

$post_ID = get_the_ID();
$link = get_post_meta($post_ID, 'link', true);
$views = (int) get_post_meta($post_ID, 'views', true);
$views++;
update_post_meta($post_ID, 'views', $views);

if (wp_redirect($link)) {
   exit;
}
