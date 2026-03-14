<?php

namespace ctrl\Hector;

use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

require_once ABSPATH . 'vendor/autoload.php';

const FONTS = [
   'default'      => 'Merriweather',
   'transitional' => 'SourceSerif4',
   'modern'       => 'InriaSerif',
   'neogrotesque' => 'Inter',
   'humanist'     => 'Lato',
   'geometric'    => 'Jost',
   'monospace'    => 'JetBrainsMono',
];

final class PDF extends Book
{
   private $mpdf;

   private function setup()
   {
      $this->type = 'pdf';

      $fontDirs = array_map(fn($font) => HECTOR_FOLDER . '_fonts' . DIRECTORY_SEPARATOR . $font, array_values(FONTS));

      foreach (FONTS as $key => $font) {
         $fontData[$key] = [
            'R'  => $font . '-Regular.ttf',
            'I'  => $font . '-Italic.ttf',
            'B'  => $font . '-Bold.ttf',
            'BI' => $font . '-BoldItalic.ttf',
         ];
      }

      $config = $this->config;
      unset($config['label']);

      $this->mpdf = new Mpdf([
         'mode'          => 'utf-8',
         'mirrorMargins' => 1,
         'dpi'           => 300,
         'img_dpi'       => 300,
         'margin_header' => 0,
         'margin_footer' => 0,
         'tempDir'       => HECTOR_FOLDER,
         'fontDir'       => $fontDirs,
         'fontdata'      => $fontData,
         'default_font'  => 'pdffont0',
         ...$config,
      ]);

      return true;
   }

   public function create()
   {
      switch_to_locale(LOCALES[$this->lang]);

      if (!$this->setup()) {
         return;
      }

      $this->content();

      restore_previous_locale();

      return $this->save();
   }

   private function add_credits()
   {
      $credits = $this->get_credits();

      $img = wp_get_attachment_image_url(\get_field('logo_print', 'options'), 'large');

      $content = <<<HTML
      <section>
         <p>{$credits['copyright']}</p>

         <dl>
            {$credits['list']}
         </dl>

         {$credits['table']}

         <figure class="has-text-align-center mb-0 mt-0">
            <a href="{$this->site_link}" target="_blank">
               <img class="mx-auto max-w-60" src="{$img}" />
            </a>
         </figure>
      </section>
      HTML;

      $this->add_section([
         'content' => $content,
      ], false);

      $this->mpdf->AddPage();
   }

   private function add_division($part)
   {
      $this->add_section([
         'content' => $this->get_division($part),
      ]);
   }

   private function add_section($spine_item, $with_section = true)
   {
      $this->mpdf->AddPage();

      $content = $this->get_section($spine_item, $with_section);

      $this->mpdf->WriteHTML($content, HTMLParserMode::HTML_BODY);
   }

   private function content()
   {
      // CSS
      $style = $this->get_css();
      $this->mpdf->WriteHTML($style, HTMLParserMode::HEADER_CSS);

      // TITLE
      $this->add_section([
         'content' => $this->get_title(false),
      ]);

      // CREDITS
      $this->add_credits();

      // FACE
      $this->add_section([
         'content' => $this->get_title(),
      ]);

      // ADD CONTENT SECTIONS
      foreach ($this->info['parts'] as $part) {
         if ($this->is_multipart) {
            $this->add_division($part);
         }

         foreach ($part['spine'] as $spine_item) {
            $this->add_section($spine_item);
         }
      }

      // BIO
      $this->add_section([
         'title'   => $this->title_bio,
         'content' => $this->get_bio(),
      ], false);

      // CTA
      $this->add_section([
         'title'   => $this->title_cta,
         'content' => $this->get_cta(),
      ]);

      // COLOPHON
      $this->add_section([
         'content' => $this->get_colophon(),
      ], false);
   }

   private function save()
   {
      $filename = Utils::get_filename($this->info['ID'], $this->version) . '.pdf';

      $this->mpdf->OutputFile(HECTOR_FOLDER . $filename);

      return $filename;
   }
}
