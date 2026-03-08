<?php

namespace ctrl\Hector;

use WC_Product_Grouped;

class Book_Prep extends WC_Product_Grouped
{
   public function make_epub()
   {
      $info = $this->get_info('epub');

      $versions = ['amazon', 'kobo', 'apple', 'google'];

      foreach ($versions as $version) {
         $info['cover'] = \get_the_post_thumbnail_url($this->get_id(), $version);
         $epub          = new Epub($info);
         $books[]       = $epub->create($version);
      }

      return $books;
   }

   public function make_html()
   {
      $info = $this->get_info('print');
      $html = new HTML($info);

      return $html->create();
   }

   public function make_pdf()
   {
      $info = $this->get_info('print');
      $pdf  = new Pdf($info);

      return $pdf->create();
   }

   private function get_info($target = 'print')
   {
      $info['ID']            = $this->get_id();
      $info['slug']          = $this->get_slug();
      $info['title']         = $this->get_title();
      $info['subtitle']      = get_field('subtitle', $this->get_id());
      $info['short_title']   = get_field('short_title', $this->get_id());
      $info['edition_title'] = get_field('edition_title', $this->get_id());
      $info['title_classes'] = get_field('title_classes', $this->get_id());
      $info['edition']       = get_field('edition', $this->get_id());
      $info['pages']         = get_field('pages', $this->get_id());
      $info['tags']          = $this->get_tag_ids();
      $info['release']       = get_post_meta($this->get_id(), 'release', true);

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
         $info['authors'][$author] = [
            'name' => get_the_author_meta('display_name', $author),
            'bio'  => [
               'pt' => get_user_meta($author, 'bio_pt', true),
               'en' => get_user_meta($author, 'bio_en', true),
               'es' => get_user_meta($author, 'bio_es', true),
            ],
            'link'  => get_the_author_meta('user_url', $author),
            'email' => get_the_author_meta('user_email', $author),
         ];
      }
      $info['author'] = Utils::get_author_names($this->get_id());

      $info['contributors'] = [];

      $contributors = get_field('contributors', $this->get_id());

      if (!empty($contributors)) {
         $info['contributors'] = $contributors;
      }

      $info['contributors'][] = [
         'name' => 'CtrlAltVerso',
         'role' => 'pbl',
      ];

      $products = $this->get_children();

      if (!empty($products)) {
         foreach ($products as $product) {
            $product = wc_get_product($product);

            $cat  = $product->get_category_ids()[0];
            $type = get_term($cat, 'product_cat')->slug;

            if ($target === $type) {
               $date_created = $product->get_date_created();

               if (!empty($date_created)) {
                  $info['release'] = $date_created->date('Y-m-d\TH:i:s\Z');
               }

               if (!empty($isbn = $product->get_global_unique_id())) {
                  $info['isbn'] = $isbn;
               }
            }

            if ('external' === $product->get_type()) {
               /** @disregard */
               $info['links'][$product->get_name()] = $product->get_product_url();
            }

            if ('single' === $product->get_type()) {
               $info['links']['CtrlAltVerso'] = get_permalink($this->get_id());
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
                  if (!empty($item['part'])) {
                     $info['parts'][$part_key]['spine'][] = $this->parse_chapter($item['part']);
                  }
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
      $excerpt = apply_filters('the_excerpt', trim($chapter->post_excerpt));

      $section_type = get_field('section_type', $chapter_ID);

      if (empty($section_type)) {
         $section_type = 'chapter';
      }

      $show_toc = get_field('show_toc', $chapter_ID);

      if (!is_bool($show_toc)) {
         $show_toc = true;
      }

      return [
         'title'            => apply_filters('the_title', $chapter->post_title, $chapter_ID),
         'author'           => get_the_author_meta('display_name', $chapter->post_author),
         'date'             => $chapter->post_date_gmt,
         'show_date'        => get_field('show_date', $chapter_ID),
         'show_title'       => get_field('show_title', $chapter_ID),
         'show_author'      => get_field('show_author', $chapter_ID),
         'show_description' => get_field('show_description', $chapter_ID),
         'show_toc'         => $show_toc,
         'section_type'     => $section_type,
         'excerpt'          => $excerpt,
         'content'          => trim($chapter->post_content),
      ];
   }
}
