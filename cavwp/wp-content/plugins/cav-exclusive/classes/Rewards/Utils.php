<?php

namespace cavEx\Rewards;

use cavWP\Utils as CavWPUtils;

class Utils
{
   public static function get_rewards($pay_type)
   {
      if (!function_exists('get_field')) {
         return [];
      }

      switch_to_blog(3);

      $unlocks = get_posts([
         'post_type' => 'unlock',
         'nopaging'  => true,
      ]);

      $rewards = [];

      $user_unlocks = get_user_meta(get_current_user_id(), 'unlocks', true);

      if (empty($user_unlocks)) {
         $user_unlocks = [];
      }

      foreach ($unlocks as $unlock) {
         $status = in_array($unlock->ID, $user_unlocks);

         $objects = get_field('object', $unlock->ID);

         if (empty($objects)) {
            $objects = [0];
         }

         $type   = get_field('type', $unlock->ID);
         $prices = get_field('prices', $unlock->ID);
         $qty    = get_field('quantity', $unlock->ID);

         if ('0' === $qty) {
            continue;
         }

         foreach ($objects as $object) {
            foreach ($prices as $price) {
               if ($price['type'] !== $pay_type) {
                  continue;
               }

               if (in_array($object, array_keys($rewards))) {
                  $rewards[$object]->title[] = $unlock->post_title;
               } else {
                  $rewards[$object] = (object) [
                     'reward_ID' => $unlock->ID,
                     'object_ID' => $object,
                     'title'     => [$unlock->post_title],
                     'summary'   => $unlock->post_excerpt,
                     'type'      => $type,
                     'price'     => $price['tier'],
                     'usd'       => $price['usd'],
                     'qty'       => $qty,
                     'status'    => $status,
                  ];
               }
            }
         }
      }

      $price = array_column($rewards, 'price');
      array_multisort($price, SORT_ASC, $rewards);

      restore_current_blog();

      return $rewards;
   }

   public static function get_rewards_by($object_target)
   {
      if (!function_exists('get_field')) {
         return;
      }

      switch_to_blog(3);

      $unlocks = get_posts([
         'post_type'  => 'unlock',
         'nopaging'   => true,
         'meta_query' => [[
            'key'     => 'object',
            'compare' => 'LIKE',
            'value'   => $object_target,
         ]],
      ]);

      $user_unlocks = get_user_meta(get_current_user_id(), 'unlocks', true);

      if (empty($user_unlocks)) {
         $user_unlocks = [];
      }

      foreach ($unlocks as $unlock) {
         $is_unlocked = in_array($unlock->ID, $user_unlocks);
         $objects = get_field('object', $unlock->ID);
         $type    = get_field('type', $unlock->ID);
         $qty = get_field('quantity', $unlock->ID);

         if ('0' === $qty) {
            continue;
         }

         foreach ($objects as $object) {
            if (
               $is_unlocked ||
               (int) $object !== (int) $object_target
            ) {
               continue;
            }

            $rewards[] = (object) [
               'reward_ID'  => $unlock->ID,
               'title'      => $unlock->post_title,
               'summary'    => $unlock->post_excerpt,
               'type'       => $type,
               'type_label' => self::get_label($type),
               'prices'     => self::parse_prices($unlock->ID),
               'qty'        => $qty,
            ];
         }
      }

      restore_current_blog();

      return $rewards;
   }

   public static function get_xp_available($user = null)
   {
      $user_ID = $user ?? get_current_user_id();

      $xp       = (int) get_user_meta($user_ID, 'xp', true);
      $xp_spent = (int) get_user_meta($user_ID, 'xp_spent', true);

      return $xp - $xp_spent;
   }

   public static function is_unlocked($object_target, $type, $user = null)
   {
      $user_ID = $user ?? get_current_user_id();
      $unlocks = get_user_meta($user_ID, 'unlocks', true);

      if(empty($unlocks)){
         $unlocks = [];
      }

      switch_to_blog(3);

      $rewards = get_posts([
         'post_type'  => 'unlock',
         'nopaging'   => true,
         'fields' => 'ids',
         'meta_query' => [
            [
               'key'     => 'object',
               'compare' => 'LIKE',
               'value'   => $object_target,
            ],
            [
               'key'     => 'type',
               'value'   => $type,
            ],
         ],
      ]);

      restore_current_blog();

      $unlocked = false;

      foreach ($rewards as $reward) {
         if (in_array($reward, $unlocks)) {
            $unlocked = true;
            break;
         }
      }

      return $unlocked;
   }

   public static function is_unlockable($reward_ID, $user = null)
   {
      $user_ID = $user ?? get_current_user_id();

      $unlocks = get_user_meta($user_ID, 'unlocks', true);

      if (empty($unlocks)) {
         $unlocks = [];
      }

      if (in_array($reward_ID, $unlocks)) {
         return false;
      }

      $xp_available = self::get_xp_available($user_ID);

      switch_to_blog(3);

      $price = self::parse_prices($reward_ID, 'xp');

      restore_current_blog();

      return $xp_available - (int) $price['tier'] > 0;
   }

   public static function join_titles($raw_titles)
   {
      $titles       = [];
      $descriptions = [];

      foreach ($raw_titles as $raw_title) {
         preg_match('/(.+) ?\(?(.+)?\)?/', $raw_title, $matches);

         if (!empty($matches[1]) && !in_array($matches[1], $titles)) {
            $titles[] = $matches[1];
         }

         if (!empty($matches[2]) && !in_array($matches[2], $descriptions)) {
            $descriptions[] = $matches[2];
         }
      }

      $title = CavWPUtils::parse_titles($titles);

      if (!empty($description)) {
         $title .= ' (' . CavWPUtils::parse_titles($descriptions) . ')';
      }

      return $title;
   }

   public static function parse_prices($reward_ID, $format = 'all')
   {
      if (!in_array($format, ['all', 'subscription', 'buy', 'xp'])) {
         return [];
      }

      switch_to_blog(3);

      $prices_field      = get_field_object('prices', $reward_ID);
      $prices            = $prices_field['value'];
      $price_type_labels = $prices_field['sub_fields'][0]['choices'];

      $all = [];

      foreach ($prices as $price) {
         if(empty($price['active'])){
            continue;
         }

         $brl = number_format((float) $price['tier'], 2, '.', '');
         $usd = number_format((float) $price['usd'], 2, '.', '');

         $price['brl']   = 'R$ ' . $brl;
         $price['usd']   = '$' . $usd;
         $price['label'] = $price_type_labels[$price['type']];
         $price['value'] = match ($price['type']) {
            'xp'           => $price['tier'] . ' XP',
            'subscription' => '<span>R$ ' . $brl . '</span><span>ou $' . $usd . '</span>',
            'buy'          => '<span>R$ ' . $brl . '</span><span>ou $' . $usd . '</span>',
            default        => '',
         };

         $all[$price['type']] = $price;
      }

      restore_current_blog();

      if ('all' === $format) {
         return $all;
      }

      return $all[$format];
   }

   public static function redeem_reward($reward_ID, $user = null)
   {
      if (!self::is_unlockable($reward_ID)) {
         return false;
      }

      $user_ID = $user ?? get_current_user_id();

      $xp_spent = (int) get_user_meta($user_ID, 'xp_spent', true);
      $price    = (int) self::parse_prices($reward_ID, 'xp')['tier'];

      $unlocks = get_user_meta($user_ID, 'unlocks', true);

      if (empty($unlocks)) {
         $unlocks = [];
      }

      $unlocks[] = $reward_ID;

      if (!update_user_meta($user_ID, 'unlocks', $unlocks)) {
         return false;
      }

      return update_user_meta($user_ID, 'xp_spent', $xp_spent + $price);
   }

   private static function get_label($key)
   {
      $labels = [
         'digital'  => esc_html__('leitura online', 'cav-exclusive'),
         'download' => esc_html__('baixar ebook', 'cav-exclusive'),
         'product'  => esc_html__('livro físico', 'cav-exclusive'),
      ];

      if (!in_array($key, array_keys($labels))) {
         return '';
      }

      return $labels[$key];
   }
}
