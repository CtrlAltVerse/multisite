<?php

namespace ctrl\Book;

use cavWP\Models\User;
use cavWP\Utils as CavWPUtils;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

require_once ABSPATH . 'vendor/autoload.php';

const FONTS = [
   // serif
   'default'      => 'Merriweather',
   'transitional' => 'SourceSerif4',
   'modern'       => 'InriaSerif',
   // sans-serif
   'neogrotesque' => 'Inter',
   'humanist'     => 'Lato',
   'geometric'    => 'Jost',
   // monospace
   'monospace' => 'JetBrainsMono',
];

final class Pdf
{
   private $info;
   private $is_multipart;
   private $lang;
   private $mpdf;
   private $site_domain;
   private $site_link;
   private $site_name;
   private $title;
   private $title_bio;
   private $year;

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

      $fontDirs = array_map(fn($font) => HECTOR_FOLDER . '_fonts' . DIRECTORY_SEPARATOR . $font, array_values(FONTS));

      foreach (FONTS as $key => $font) {
         $fontData[$key] = [
            'R'  => $font . '-Regular.ttf',
            'I'  => $font . '-Italic.ttf',
            'B'  => $font . '-Bold.ttf',
            'BI' => $font . '-BoldItalic.ttf',
         ];
      }

      $this->mpdf = new Mpdf([
         'mode'          => 'utf-8',
         'mirrorMargins' => 1,
         'dpi'           => 200,
         'img_dpi'       => 96,
         'format'        => [160, 230],
         'margin_header' => 0,
         'margin_footer' => 0,
         'margin_top'    => 14.4,
         'margin_left'   => 11,
         'margin_bottom' => 24,
         'margin_right'  => 11,
         'tempDir'       => HECTOR_FOLDER,
         'fontDir'       => $fontDirs,
         'fontdata'      => $fontData,
         'default_font'  => 'pdffont0',
      ]);

      $css = get_option('cav_hector_epub_style', '');

      $this->mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
   }

   public function create()
   {
      switch_to_locale(LOCALES[$this->lang]);

      $this->add_face();
      $this->add_credits();
      $this->add_title();

      // ADD CONTENT SECTIONS
      foreach ($this->info['parts'] as $part) {
         if ($this->is_multipart) {
            $this->add_division($part);
         }

         foreach ($part['spine'] as $spine_item) {
            $this->add_section($spine_item);
         }
      }

      $this->add_bio();
      $this->add_colophon();

      $filename = Utils::get_filename($this->info['ID'], 'br') . '.pdf';

      $this->mpdf->OutputFile(HECTOR_FOLDER . $filename);

      restore_previous_locale();

      return $filename;
   }

   private function add_bio()
   {
      $content = <<<HTML
         <h1>{$this->title_bio}</h1>
      HTML;

      foreach ($this->info['authors'] as $author_ID => $author) {
         $this->mpdf->AddPage();

         $img = get_avatar_url($author_ID, ['size' => 666]);

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
         <section>
            <img src="{$img}" alt="" class="is-style-rounded" />
            <h2>{$author['name']}</h2>
            {$bio}
            <ul>
               {$links}
            </ul>
         </section>
         HTML;
      }

      $this->mpdf->WriteHTML($content);
   }

   private function add_colophon()
   {
      $this->mpdf->AddPage();

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

      $image_url = wp_get_attachment_image_url(\get_field('logo_print', 'options'), 'large');

      $content = <<<HTML
      <section>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
      <div>
         <p class="has-medium-font-size has-text-align-center"><strong>{$title}</strong></p>
         <figure>
            <a href="{$this->site_link}" target="_blank">
               <img class="mx-auto w-50" src="{$image_url}" />
            </a>
         </figure>
         <ul class="list-none has-text-align-center">
            <li><a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></li>
            {$links}
         </ul>
      </div>
      </section>
      HTML;

      $this->mpdf->WriteHTML($content);
   }

   private function add_credits()
   {
      $all_rights = esc_html__('Todos os direitos reservados.', 'ctrl');
      $author     = rtrim($this->info['author'], '.');

      $list = '';

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

      $main_author = Utils::invert_name(array_values($this->info['authors'])[0]['name']);

      $content = <<<HTML
         <section>
            <p>Copyright © {$this->year} by {$author}. {$all_rights}</p>
            <dl>
               <dt>{$this->title}</dt>
               <dd>{$this->info['author']}</dd>

               {$list}

               <dt>{$this->site_name}</dt>
               <dd><a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></dd>
            </dl>

            <table class="has-monospace-font-family has-small-text-size border-y w-100 no-border mt-10">
               <tbody>
                  <tr>
                     <td class="pt-5 pb-5 pr-6 pl-6 align-top">
                        S4343d
                     </td>
                     <td class="pt-5 pb-5 pr-6 pl-6">
                        <p>{$main_author}</p>
                        <p class="has-text-align-justify indent">{$this->title} / {$author}. - CtrlAltVerso, {$this->year}.</p>
                        <p class="has-text-align-justify indent">16 x 23cm</p>
                        <br/>
                        <p>ISBN: </p>
                        <br/>
                        <p>1. Ficção brasileira. I. Título.</p>
                     </td>
                  </tr>
                  <tr>
                     <td></td>
                     <td class="has-text-align-right">
                        <p>CDD: </p>
                        <p>CDU: </p>
                     </td>
                  </tr>
               </tbody>
            </table>
         </section>
      HTML;

      $this->mpdf->WriteHTML($content);
      $this->mpdf->AddPage();
   }

   private function add_division($part)
   {
      $this->mpdf->AddPage();

      if (!empty($part['subtitle'])) {
         $subtitle = "<p class=\"has-medium-font-size mt-2\">{$part['subtitle']}</p>";
      }

      $content = <<<XHTML
      <section>
      <div class="valign-center">
         <h1 class="has-large-font-size">{$part['title']}</h1>
         {$subtitle}
      </div>
      </section>
      XHTML;

      $this->mpdf->WriteHTML($content);
   }

   private function add_face()
   {
      $content = <<<HTML
      <section>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
      <div class="h1 has-text-align-center">{$this->title}</div>
      </section>
      HTML;

      $this->mpdf->WriteHTML($content);
      $this->mpdf->AddPage();
   }

   private function add_section($spine_item)
   {
      $this->mpdf->AddPage();

      $content = '<section>';

      if ($spine_item['show_title'] ?? false && !empty($spine_item['title'])) {
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

      $content .= '</section>';

      $this->mpdf->WriteHTML($content);
   }

   private function add_title()
   {
      $content = <<<HTML
      <section>
      <div class="has-text-align-center h2 mt-0 mb-0">{$this->info['author']}</div>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
      <div class="h1 has-text-align-center mb-0">{$this->title}</div>
      <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
      <div class="has-text-align-center has-medium-font-size">CtrlAltVerso</div>
      <div class="has-text-align-center has-medium-font-size">{$this->year}</div>
      </section>
      HTML;

      $this->mpdf->WriteHTML($content);
      $this->mpdf->AddPage();
   }
}
