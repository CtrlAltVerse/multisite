<?php

namespace ctrl\Book;

use cavWP\Utils as CavWPUtils;
use WC_Product_Grouped;

class Book extends WC_Product_Grouped
{
   public function make_epub()
   {
      $info = $this->get_info('epub');

      $versions = ['amazon', 'kobo', 'apple', 'google'];

      foreach ($versions as $version) {
         $info['cover'] = \get_the_post_thumbnail_url($this->get_id(), $version);
         $epub          = new Epub($version, $info);
         $books[]       = $epub->create();
      }

      return $books;
   }

   public function make_pdf()
   {
      // mPDF https://mpdf.github.io/
      // https://dompdf.github.io/
   }

   private function get_info($target = 'print')
   {
      $info['ID']            = $this->get_id();
      $info['slug']          = $this->get_slug();
      $info['title']         = $this->get_title();
      $info['subtitle']      = get_field('subtitle', $this->get_id());
      $info['short_title']   = get_field('short_title', $this->get_id());
      $info['edition_title'] = get_field('edition_title', $this->get_id());
      $info['tags']          = $this->get_tag_ids();

      $attributes = $this->get_attributes();

      foreach ($attributes as $key => $attribute) {
         $key = str_replace('pa_', '', $key);

         $info['attributes'][$key] = get_term($attribute['options'][0])->slug;
      }

      $info['description'] = apply_filters('the_excerpt', $this->get_short_description());

      $series = get_the_terms($this->get_id(), 'series');

      if (empty($series)) {
         $info['series']['title']    = $series[0]->name;
         $info['series']['type']     = get_term_meta($series[0]->term_id, 'type', true);
         $info['series']['uuid']     = get_term_meta($series[0]->term_id, 'uuid', true);
         $info['series']['position'] = get_field('series_position', $this->get_id());
      }

      $authors = get_field('authors', $this->get_id());

      foreach ($authors as $author) {
         $authors_names[] = get_the_author_meta('display_name', $author);

         $info['authors'][$author] = [
            'name'   => get_the_author_meta('display_name', $author),
            'avatar' => get_avatar_url($author, [
               'size' => 180,
            ]),
            'bio'            => get_the_author_meta('description', $author),
            'link'           => get_the_author_meta('user_url', $author),
            'email'          => get_the_author_meta('user_email', $author),
            'amazon-profile' => get_the_author_meta('amazon-profile', $author),
         ];
      }
      $info['author'] = CavWPUtils::parse_titles($authors_names);

      $info['contributors'] = get_field('contributors', $this->get_id());

      $products = $this->get_children();

      if (!empty($products)) {
         foreach ($products as $product) {
            $product = wc_get_product($product);

            $cat  = $product->get_category_ids()[0];
            $type = get_term($cat, 'product_cat')->slug;

            if ($target === $type) {
               $info['release'] = $product->get_date_created();

               if (!empty($isbn = $product->get_global_unique_id())) {
                  $info['isbn'] = $isbn;
               }
            }

            if ('external' === $product->get_type()) {
               /** @disregard */
               $info['links'][] = $product->get_product_url();
            }
         }
      }

      $parts = get_field('parts', $this->get_id());

      if (!empty($parts)) {
         foreach ($parts as $part_key => $part) {
            $info['parts'][$part_key] = [
               'title'    => $part['title'],
               'subtitle' => $part['subtitle'],
            ];

            foreach ($part['spine'] as $item) {
               if ('print' === $target && 'epub' === $item['type']) {
                  continue;
               }

               if ('epub' === $target && 'print' === $item['type']) {
                  continue;
               }

               if (in_array($item['type'], ['chapter', 'print', 'epub'])) {
                  $info['parts'][$part_key]['spine'][] = $this->parse_chapter($item['part']);
               }

               if ('custom' === $item['type'] && 'print' === $target) {
                  $info['parts'][$part_key]['spine'][] = wp_get_attachment_url($item['custom']);
               }
            }
         }
      }

      return $info;
   }

   private function parse_chapter($chapter_ID)
   {
      $chapter = get_post($chapter_ID);

      $content = apply_filters('the_content', $chapter->post_content);
      $excerpt = apply_filters('the_excerpt', $chapter->post_excerpt);

      return [
         'title'            => apply_filters('the_title', $chapter->post_title, $chapter_ID),
         'author'           => get_the_author_meta('display_name', $chapter->post_author),
         'date'             => $chapter->post_date_gmt,
         'show_date'        => get_field('show_date', $chapter_ID),
         'show_title'       => get_field('show_title', $chapter_ID),
         'show_author'      => get_field('show_author', $chapter_ID),
         'show_description' => get_field('show_description', $chapter_ID),
         'show_toc'         => get_field('show_toc', $chapter_ID),
         'section_type'     => get_field('section_type', $chapter_ID),
         'excerpt'          => $excerpt,
         'content'          => $content,
      ];
   }
}
