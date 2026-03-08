<?php

namespace ctrl\Hector;

use cavWP\Models\User;
use cavWP\Utils as CavWPUtils;
use NumberFormatter;

class Book
{
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
   protected $year;

   public function __construct($info)
   {
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

      foreach ($this->info['authors'] as $author_ID => $author) {
         if ('epub' === $this->type) {
            $img = '../assets/images/avatar-' . $author_ID . '.jpg';
         } else {
            $img = get_avatar_url($author_ID, ['size' => 200]);
         }

         $links = '';

         if (!empty($author['link'])) {
            $site_text = esc_html__('Site pessoal', 'ctrl');
            $links .= "<li><a href=\"{$author['link']}\" target=\"_blank\">{$site_text}</a></li>";
         }

         $author_o = new User($author_ID);
         $socials  = $author_o->get_socials();

         foreach ($socials as $social) {
            $links .= "<li><a href=\"{$social['profile']}\" target=\"_blank\">{$social['name']}</a></li>";
         }

         $bio_content = explode(PHP_EOL, $author['bio'][$this->lang]);
         $bio         = implode(PHP_EOL, array_map(fn($line) => '<p class="has-text-align-left">' . $line . '</p>', $bio_content));

         $content .= <<<HTML
         <section class="break-inside-avoid" epub:type="bio" role="doc-credit" id="bio-{$author_ID}">
            <figure class="wp-block-image is-style-rounded">
               <img src="{$img}" alt="" />
            </figure>
            <h2>{$author['name']}</h2>
            {$bio}
            <ul class="wp-block-list is-style-square">
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

      if ('pdf' === $this->type) {
         $spacing = '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
      }

      $bg_color = '';

      if ('epub' === $this->type) {
         $bg_color = 'has-black-sky-background-color';
      }

      return <<<HTML
      <div class="page-bottom">
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
      </div>
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

      if (!empty($this->info['isbn'])) {
         $isbn = 'ISBN: ' . $this->info['isbn'];
      }
      $edition = $this->info['edition'] ?? 1;
      $pages   = $this->info['pages']   ?? 96;

      $categories_label = '';
      $category_cdd     = '';
      $category_cdu     = '';

      $main_category = $this->info['attributes']['tipo'] ?? false;

      if (!empty($main_category)) {
         $label = get_field('label', 'term:' . $main_category);

         if (!empty($label)) {
            $categories_label .= '1. ' . get_field('label', 'term:' . $main_category);
            $category_cdd     .= get_field('cdd', 'term:' . $main_category);
            $category_cdu     .= get_field('cdu', 'term:' . $main_category);

            if (!empty($this->info['tags'])) {
               foreach ($this->info['tags'] as $tag_ID) {
                  $candidates[] = [
                     'cdd'   => get_field('cdd', 'term:' . $tag_ID),
                     'label' => get_field('label', 'term:' . $tag_ID),
                  ];
               }

               if (!empty($candidates)) {
                  $candidate = array_reduce($candidates, fn($carry, $candidate) => (int) $candidate['cdd'] < $carry, 9999);

                  $categories_label .= ' 2. ' . $candidate['label'];
                  $category_cdd     .= '.' . $candidate['cdd'];
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
                  <div class="has-text-align-justify">{$pages} p. 16 x 23cm</div>
                  <br/>
                  <div>{$isbn}</div>
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

      $converter = new Theme_JSON_Converter();
      $css .= $converter->get_css();

      return $css;
   }

   protected function get_cta($version)
   {
      $link = '';

      if (!empty($this->info['links'])) {
         foreach ($this->info['links'] as $stone_name => $store_link) {
            if (str_contains(strtolower($stone_name), $version) || str_contains($store_link, $version)) {
               break;
            }
         }

         $link_text = sprintf(
            esc_html__('%s na loja %s', 'ctrl'),
            $this->title,
            $stone_name,
         );

         $link = "<p class=\"has-text-align-justify mt-2\"><a href=\"{$store_link}\" target=\"_blank\">{$link_text}</a></p>";
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
      <div class="page-center">
         {$spacing}
         <h1 class="has-text-align-center has-large-font-size mb-0 mt-0">{$part['title']}</h1>
         {$subtitle}
      </div>
      HTML;
   }

   protected function get_section($spine_item, $with_section = true, $apply_filter = true)
   {
      $content = '';

      if ($with_section) {
         if ('epub' === $this->type) {
            $role = '';

            if (!empty($spine_item['section_role'])) {
               $role = "role=\"doc-{$spine_item['section_role']}\" id=\"{$spine_item['section_role']}\"";
            }

            $content .= "<section epub:type=\"{$spine_item['body_type']}\" {$role}>";
         } else {
            $content .= '<section>';
         }
      }

      if ($spine_item['show_title'] ?? true && !empty($spine_item['title'])) {
         $content .= "<h1>{$spine_item['title']}</h1>";
      }

      if ($spine_item['show_description'] ?? false && !empty($spine_item['excerpt'])) {
         $content .= "<p class=\"section-description\">{$spine_item['excerpt']}</p>";
      }

      if ($spine_item['show_author'] ?? false && !empty($spine_item['author'])) {
         $content .= "<p class=\"section-author\">{$spine_item['author']}</p>";
      }

      $content .= Utils::parse_blocks($spine_item['content']);

      if ($spine_item['show_date'] ?? false) {
         $date_formats = [
            'en' => 'F jS, Y',
            'pt' => 'j \d\e F, Y',
            'es' => 'j \d\e F, Y',
         ];

         $date = date_i18n($date_formats[$this->lang], $spine_item['date'], true);

         $content .= "<p class=\"section-date\">{$date}</p>";
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
         $title_main = str_replace(' ', '<br/', $title_main);
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

         $footer = <<<HTML
         <div>
            <p class="has-text-align-center has-medium-font-size">CtrlAltVerso</p>
            <p class="has-text-align-center">{$edition}</p>
            <p class="has-text-align-center">{$this->year}</p>
         </div>
         HTML;
      }

      $spacing = '';

      if ('pdf' === $this->type) {
         $spacing = '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>';
      }

      $classes = $face ? 'page-between' : 'page-center';

      return <<<HTML
      <div class="{$classes}">
         {$header}
         {$spacing}
         <div class="mt-8 mb-8">
            {$middle}
         </div>
         {$spacing}
         {$footer}
      </div>
      HTML;
   }
}
