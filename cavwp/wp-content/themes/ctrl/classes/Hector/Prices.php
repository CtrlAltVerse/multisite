<?php

namespace ctrl\Hector;

use WC_Product_Grouped;
use WP_Error;

final class Prices
{
   private $currencies;
   private $exchanges;

   public function __construct()
   {
      $this->exchanges  = $this->get_exchanges();
      $this->currencies = \get_field('currencies', 'option');
   }

   public static function get_exchanges()
   {
      $request = wp_remote_get('https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/usd.json', [
         'cache_duration' => '1 week',
      ]);

      if (is_wp_error($request)) {
         return $request;
      }

      if (200 !== \wp_remote_retrieve_response_code($request)) {
         return new WP_Error('invalid_response', 'Invalid response from currency API');
      }

      $body = json_decode(\wp_remote_retrieve_body($request), true);

      return $body['usd'];
   }

   public function get_prices($book_ID, $currencies)
   {
      $chars_count = get_post_meta($book_ID, 'chars_count', true);

      if (empty($chars_count)) {
         return [];
      }

      $release = get_post_meta($book_ID, 'release', true);
      $is_new  = strtotime($release) >= time() - 6 * MONTH_IN_SECONDS;

      $product = new WC_Product_Grouped($book_ID);

      $attributes = $product->get_attributes();

      foreach ($attributes as $key => $attribute) {
         $key = str_replace('pa_', '', $key);

         if ('lang' !== $key) {
            continue;
         }

         $lang = get_term($attribute['options'][0])->slug;
      }

      $usd = $chars_count / 1000 * 0.01;

      if ($is_new) {
         $usd *= 1.15;
      }

      $prices = [];

      foreach ($this->currencies as $currency_data) {
         foreach ($currencies as $currency) {
            if ($currency_data['code'] !== $currency) {
               continue;
            }
            $price = $usd * $this->exchanges[strtolower($currency)] * (float) $currency_data['base'];

            if ($price < (float) $currency_data['min']) {
               $price = (float) $currency_data['min'];
            }

            if ($price > (float) $currency_data['max']) {
               $price = (float) $currency_data['max'];
            }

            $price = number_format($price, $currency_data['has_decimal'] ? 2 : 0, '.', '');

            $prices[$currency] = $price;
            break;
         }
      }

      return $prices;
   }
}
