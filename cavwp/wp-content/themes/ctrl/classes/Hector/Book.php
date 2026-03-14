<?php

namespace ctrl\Hector;

use cavWP\Models\User;
use cavWP\Utils as CavWPUtils;
use NumberFormatter;

class Book
{
   protected $config;

   /** ePub only */
   protected $images = [];
   protected $info;
   protected $is_multipart;
   protected $lang;
   protected $site_domain;
   protected $site_link;
   protected $site_name;
   protected $title;
   protected $title_bio;
   protected $title_cta;
   protected $title_nav;
   protected $type;
   protected $version;
   protected $year;

   public function __construct($info, $version, $config)
   {
      $this->version      = $version;
      $this->config       = $config;
      $this->info         = $info;
      $this->title        = $info['title'];
      $this->lang         = $info['attributes']['lang'] ?? 'pt';
      $this->is_multipart = count($this->info['parts']) > 1;
      $this->year         = date('Y', strtotime($info['release']));
      $this->site_name    = get_bloginfo('name');
      $this->site_link    = home_url();
      $this->site_domain  = CavWPUtils::clean_domain($this->site_link);

      if (count($this->info['authors']) === 1) {
         $this->title_bio = esc_attr__('Sobre o autor', 'ctrl');
      } else {
         $this->title_bio = esc_attr__('Sobre os autores', 'ctrl');
      }

      $this->title_cta = esc_html__('Obrigado', 'ctrl');
      $this->title_nav = esc_html__('Sumário', 'ctrl');
   }

   protected function get_bio()
   {
      $content = '';
      $img     = '';

      foreach ($this->info['authors'] as $author_ID => $author) {
         if ('epub' === $this->type) {
            foreach ($this->images as $image_name => $image) {
               if (str_starts_with($image_name, "avatar-{$author_ID}.")) {
                  $img = $image['path'];
               }
            }
         } else {
            $img = get_avatar_url($author_ID, ['size' => 200]);
         }

         $links = '';

         if (!empty($author['link'])) {
            $site_text = esc_html__('Site pessoal', 'ctrl');
            $link      = CavWPUtils::clean_domain($author['link']);
            $links .= "<li><a href=\"{$author['link']}\" data-href=\"{$link}\" target=\"_blank\">{$site_text}</a></li>";
         }

         $author_o = new User($author_ID);
         $socials  = $author_o->get_socials();

         foreach ($socials as $social) {
            $link = CavWPUtils::clean_domain($social['profile']);
            $links .= "<li><a href=\"{$social['profile']}\" data-href=\"{$link}\" target=\"_blank\">{$social['name']}</a></li>";
         }

         $bio_content = explode(PHP_EOL, $author['bio'][$this->lang]);
         $bio         = implode(PHP_EOL, array_map(fn($line) => '<p class="has-text-align-left">' . $line . '</p>', $bio_content));

         $content .= <<<HTML
         <section class="break-inside-avoid" epub:type="bio" role="doc-credit" id="bio-{$author_ID}">
            <figure class="wp-block-image is-style-rounded mt-0 mb-0">
               <img src="{$img}" alt="" />
            </figure>
            <h2>{$author['name']}</h2>
            {$bio}
            <ul class="wp-block-list is-style-square mb-0">
               {$links}
            </ul>
         </section>
         HTML;
      }

      return $content;
   }

   protected function get_colophon()
   {
      $title      = mb_strtoupper(esc_html__('Uma publicação', 'ctrl'));
      $site_links = get_field('links', 'options')[0]['group'];

      $links = '';

      if (!empty($site_links)) {
         foreach ($site_links as $site_link) {
            $link_domain = CavWPUtils::clean_domain($site_link['link']);

            $links .= <<<HTML
               <li><a href="{$site_link['link']}" target="_blank">{$link_domain}</a></li>
            HTML;
         }
      }

      if ('epub' === $this->type) {
         $img = '../assets/images/CtrlAltVerso.png';
      } else {
         $img = wp_get_attachment_image_url(\get_field('logo_print', 'options'), 'large');
      }

      $spacing = '';

      if ('html' === $this->type && !empty($info['extra_pages'])) {
         for ($i = 0; $i < (int) $info['extra_pages']; $i++) {
            $spacing .= '<div class="break-after-always"></div>';
         }
      }

      if ('pdf' === $this->type) {
         $spacing = '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
      }

      $bg_color = '';

      if ('epub' === $this->type) {
         $bg_color = 'has-black-sky-background-color';
      }

      return <<<HTML
      {$spacing}
      <p class="has-medium-font-size has-text-align-center"><strong>{$title}</strong></p>
      <figure class="{$bg_color} has-text-align-center no-reformat">
         <a href="{$this->site_link}" target="_blank">
            <img class="mx-auto max-w-50" src="{$img}" />
         </a>
      </figure>
      <ul class="wp-block-list is-style-none has-text-align-center no-reformat">
         <li><a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></li>
         {$links}
      </ul>
      HTML;
   }

   protected function get_credits()
   {
      $title = $this->title;

      if (!empty($this->info['subtitle'])) {
         $title .= ': ' . $this->info['subtitle'];
      }

      // LIST ============================ \/
      $list = <<<HTML
         <dt>{$title}</dt>
         <dd>{$this->info['author']}</dd>
      HTML;

      if (!empty($this->info['series']['title'])) {
         if (!empty($this->info['series']['position'])) {
            $series_title = sprintf(
               esc_attr__('Livro %d da série', 'ctrl'),
               $this->info['series']['position'],
            );
         } else {
            $series_title = esc_attr__('Da série', 'ctrl');
         }

         $list .= <<<XML
         <dt>{$series_title}</dt>
         <dd>{$this->info['series']['title']}</dd>
         XML;
      }

      if (!empty($this->info['contributors'])) {
         $contributors = [];

         foreach ($this->info['contributors'] as $contributor) {
            if (in_array($contributor['role'], array_keys($contributors))) {
               $contributors[$contributor['role']][] = $contributor['name'];
            } else {
               $contributors[$contributor['role']] = [$contributor['name']];
            }
         }

         foreach ($contributors as $role => $contributors_names) {
            $role  = Utils::get_roles($role);
            $names = CavWPUtils::parse_titles($contributors_names);

            $list .= <<<HTML
            <dt>{$role}</dt>
            <dd>{$names}</dd>
            HTML;
         }
      }

      // COPYRIGHT ============================ \/
      $all_rights = esc_html__('Todos os direitos reservados.', 'ctrl');
      $author     = rtrim($this->info['author'], '.');

      // FICHA ================================ \/
      $main_author = Utils::invert_name(array_values($this->info['authors'])[0]['name']);
      $cutter      = get_user_meta(array_keys($this->info['authors'])[0], 'cutter', true);
      $letter      = strtolower(substr($this->title, 0, 1));
      $author      = rtrim($this->info['author'], '.');
      $isbn        = '';

      if (!empty($this->info['versions'][$this->version]['isbn'])) {
         $isbn = 'ISBN: ' . $this->info['versions'][$this->version]['isbn'];
      }
      $edition = $this->info['edition'] ?? 1;
      $pages   = $this->info['pages']   ?? 96;

      $categories_label = '';
      $category_cdd     = '';
      $category_cdu     = '';

      $main_category_slug = $this->info['attributes']['tipo'] ?? false;

      if (!empty($main_category_slug)) {
         $main_term = get_term_by('slug', $main_category_slug, 'pa_tipo');
         $label     = get_field('label', 'term_' . $main_term->term_id);

         if (!empty($label)) {
            $categories_label .= '1. ' . $label;
            $category_cdd     .= get_field('cdd', 'term_' . $main_term->term_id);
            $category_cdu     .= get_field('cdu', 'term_' . $main_term->term_id);

            if (!empty($this->info['tags'])) {
               foreach ($this->info['tags'] as $tag_ID) {
                  $cdd = get_field('cdd', 'term_' . $tag_ID);

                  if (empty($cdd)) {
                     continue;
                  }

                  $candidates[$cdd] = get_field('label', 'term_' . $tag_ID);
               }

               if (!empty($candidates)) {
                  ksort($candidates);

                  foreach ($candidates as $cdd => $label) {
                     if (!empty($label)) {
                        $categories_label .= ' 2. ' . $label;
                     }

                     if (!str_contains($category_cdd, '.')) {
                        $category_cdd .= '.' . $cdd;
                     }
                     break;
                  }
               }
            }

            if (!empty($category_cdd)) {
               $category_cdd = 'CDD: ' . $category_cdd;
            }

            if (!empty($category_cdu)) {
               $category_cdu = 'CDU: ' . $category_cdu;
            }
         }
      }

      $table = <<<HTML
      <figure class="wp-block-table is-style-filecard">
      <table>
         <tbody>
            <tr>
               <td class="pt-3 pl-4 align-top">
                  {$cutter}{$letter}
               </td>
               <td class="pt-3 pr-4 align-top">
                  <div>{$main_author}</div>
                  <div class="has-text-align-justify">{$title} / {$author}. - {$edition}ª ed. - CtrlAltVerso, {$this->year}.</div>
                  <div class="has-text-align-justify">{$pages} p. {$this->config['page_size']}</div>
                  <br/>
                  <div class="isbn">{$isbn}</div>
                  <br/>
                  <div>{$categories_label}. I. Título.</div>
               </td>
            </tr>
            <tr>
               <td></td>
               <td class="pb-3 pr-4 has-text-align-right">
                  <p>{$category_cdd}</p>
                  <p>{$category_cdu}</p>
               </td>
            </tr>
         </tbody>
      </table>
      </figure>
      HTML;

      return [
         'list'      => $list,
         'copyright' => "Copyright © {$this->year} by {$author}. {$all_rights}",
         'table'     => $table,
      ];
   }

   protected function get_css()
   {
      $css = get_option('cav_hector_epub_style', '');

      $converter = new Theme_JSON_Converter($this->type);
      $css .= $converter->get_css();

      if ('html' === $this->type) {
         $css .= <<<HTML
         @media print{
            @page{
               size: {$this->config['format'][0]}mm {$this->config['format'][1]}mm;
               margin-top: {$this->config['margin_top']}mm;
               margin-bottom: {$this->config['margin_bottom']}mm;
               margin-left: {$this->config['margin_left']}mm;
               margin-right: {$this->config['margin_right']}mm;
            }
            @page :right {
               margin-left: {$this->config['margin_right']}mm;
               margin-right: {$this->config['margin_left']}mm;
            }
         }
         HTML;
      }

      return $css;
   }

   protected function get_cta()
   {
      $link = '';

      if (!empty($this->info['versions'][$this->version]['link'])) {
         $store_link = $this->info['versions'][$this->version]['link'];

         $link_text = sprintf(
            esc_html__('%s na loja %s', 'ctrl'),
            $this->title,
            $this->info['versions'][$this->version]['name'],
         );

         $store_link_lite = CavWPUtils::clean_domain($store_link);

         $link = <<<HTML
         <p class="has-text-align-justify mt-2"><a href="{$store_link}" data-href="{$store_link_lite}" target="_blank">{$link_text}</a></p>
         HTML;
      }

      $line1 = esc_html__('Agradecemos sua compra e principalmente pela leitura deste livro. Isto vale muito para nós. ', 'ctrl');
      $line2 = esc_html__('Se puder, deixe sua avaliação e um comentário na loja que comprou.', 'ctrl');

      return <<<HTML
         <p class="has-text-align-justify">{$line1}</p>
         <p class="has-text-align-justify">{$line2}</p>
         {$link}
      HTML;
   }

   protected function get_division($part)
   {
      $subtitle = '';

      if (!empty($part['subtitle'])) {
         $subtitle = "<p class=\"has-text-align-center has-medium-font-size\">{$part['subtitle']}</p>";
      }

      $spacing = '';

      if ('pdf' === $this->type) {
         $spacing = '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
      }

      return <<<HTML
      {$spacing}
      <h1 class="has-text-align-center has-large-font-size mb-0 mt-0">{$part['title']}</h1>
      {$subtitle}
      HTML;
   }

   protected function get_section($spine_item, $with_section = true)
   {
      $spine_item['layout'] = empty($spine_item['layout']) ? [] : $spine_item['layout'];

      $content = '';
      $classes = implode(' ', $spine_item['layout']);

      if ($with_section) {
         if ('epub' === $this->type) {
            $role = '';

            if (!empty($spine_item['section_role'])) {
               $role = "role=\"doc-{$spine_item['section_role']}\" id=\"{$spine_item['section_role']}\"";
            }

            $content .= "<section class=\"{$classes}\" epub:type=\"{$spine_item['body_type']}\" {$role}>";
         } else {
            $content .= "<section class=\"{$classes}\">";
         }
      }

      if (in_array('blank-before', $spine_item['layout']) && 'epub' !== $this->type) {
         $content .= '<div class="break-after-always"></div>';
      }

      if ($spine_item['show']['title'] ?? true && !empty($spine_item['title'])) {
         $content .= "<h1 class=\"session-title\">{$spine_item['title']}</h1>";
      }

      if ($spine_item['show']['description'] ?? false && !empty($spine_item['excerpt'])) {
         $content .= "<p class=\"section-description\">{$spine_item['excerpt']}</p>";
      }

      if ($spine_item['show']['author'] ?? false && !empty($spine_item['author'])) {
         $content .= "<p class=\"section-author\">{$spine_item['author']}</p>";
      }

      $content .= Utils::parse_blocks($spine_item['content']);

      if ($spine_item['show']['date'] ?? false) {
         $date_formats = [
            'en' => 'F jS, Y',
            'pt' => 'j \d\e F, Y',
            'es' => 'j \d\e F, Y',
         ];

         $date = date_i18n($date_formats[$this->lang], $spine_item['date'], true);

         $content .= "<p class=\"section-date\">{$date}</p>";
      }

      if (in_array('blank-after', $spine_item['layout']) && 'epub' !== $this->type) {
         $content .= '<div class="page-clean break-before-always"></div>';
      }

      if ($with_section) {
         $content .= '</section>';
      }

      return $content;
   }

   protected function get_title($face = true)
   {
      $subtitle = '';

      if (!empty($this->info['subtitle'])) {
         $subtitle .= <<<HTML
         <br/><span class="has-medium-font-size" epub:type="subtitle" role="doc-subtitle">
            {$this->info['subtitle']}
         </span>
         HTML;
      }

      $title_classes = $this->info['title_classes'] ?? 'has-text-align-center mb-0 mt-0';

      $title_main = $this->title;

      if (str_contains($title_classes, 'spaces_to_nl')) {
         $title_main = str_replace(' ', '<br/>', $title_main);
      }

      if ('epub' !== $this->type) {
         $title_classes .= ' mt-12';
      }

      // TITLE
      $title = <<<HTML
      <h1 class="{$title_classes}" epub:type="fulltitle">
         <span class="has-x-large-font-size" epub:type="title">{$title_main}</span>
         {$subtitle}
      </h1>
      HTML;

      // AUTHOR
      $author = <<<HTML
         <div class="has-text-align-center has-large-font-size mt-0 mb-0">{$this->info['author']}</div>
      HTML;

      // EDITION
      $formatter      = new NumberFormatter(LOCALES[$this->lang], NumberFormatter::ORDINAL);
      $edition_number = $formatter->format($this->info['edition'] ?? 1);
      $edition_number = str_replace('º', 'ª', $edition_number);

      $edition = sprintf(
         __('%s Edição', 'ctrl'),
         $edition_number,
      );

      $header = '';
      $footer = '';

      if (!$face) {
         $middle = $title;
      } else {
         if ('epub' === $this->type) {
            $header = $title;
            $middle = $author;
         } else {
            $header = $author;
            $middle = $title;
         }

         $publisher      = '';
         $publisher_logo = '';

         if ('epub' === $this->type) {
            $publisher = '<p class="has-text-align-center has-medium-font-size">CtrlAltVerso</p>';
         } else {
            $img = \wp_get_attachment_image_url(\get_field('logo_print', 'options'), 'medium');

            $publisher_logo = <<<HTML
               <img class="block max-w-3xs mx-auto mt-9" src="{$img}" alt="CtrlAltVerso" />
            HTML;
         }

         $footer = <<<HTML
         <div>
            {$publisher}
            <p class="has-text-align-center">{$edition}</p>
            <p class="has-text-align-center">{$this->year}</p>
            {$publisher_logo}
         </div>
         HTML;
      }

      $spacing = '';

      if ('pdf' === $this->type) {
         $spacing = '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
      }

      return <<<HTML
      {$header}
      {$spacing}
      <div class="mt-8 mb-8">
         {$middle}
      </div>
      {$spacing}
      {$footer}
      HTML;
   }
}
