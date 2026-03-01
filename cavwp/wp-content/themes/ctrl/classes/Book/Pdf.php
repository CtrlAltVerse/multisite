<?php

namespace ctrl\Book;

use Mpdf\Mpdf;

require_once ABSPATH . 'vendor/autoload.php';

define('FORMATS', [
   'us' => ['155.5', '228.6'],
   'br' => ['160', '230'],
]);
define('MARGINS', [
   'us' => ['margin_left' => 12,
      'margin_right'      => 17,
      'margin_top'        => 18,
      'margin_bottom'     => 25],
   'br' => ['margin_left' => 13.5,
      'margin_right'      => 20,
      'margin_top'        => 18,
      'margin_bottom'     => 39, ],
]);

// https://dompdf.github.io/

final class Pdf
{
   private $current_page = 1;
   private $info;
   private $is_multipart;
   private $lang;
   private $mpdf;
   private $title;
   private $version;

   public function __construct($version, $info)
   {
      $this->info         = $info;
      $this->title        = $info['title'];
      $this->version      = $version;
      $this->lang         = $info['attributes']['lang'];
      $this->is_multipart = count($this->info['parts']) > 1;

      $this->mpdf = new Mpdf([
         'mode'          => 'utf-8',
         'mirrorMargins' => 1,
         'dpi'           => 300,
         'img_dpi'       => 300,
         'format'        => FORMATS[$version],
         'margin_header' => 0,
         'margin_footer' => 0,
         'tempDir'       => HECTOR_FOLDER,
         ...MARGINS[$version],
      ]);
   }

   public function create()
   {
      switch_to_locale(LOCALES[$this->lang]);

      // ADD CONTENT SECTIONS
      foreach ($this->info['parts'] as $part) {
         if ($this->is_multipart) {
            $this->add_division($part);
         }

         foreach ($part['spine'] as $spine_item) {
            $this->add_section($spine_item);
         }
      }

      $filename = Utils::get_filename($this->info['ID'], $this->version) . '.pdf';

      $this->mpdf->OutputFile(HECTOR_FOLDER . $filename);

      restore_previous_locale();

      return $filename;
   }

   private function add_division($part)
   {
      $this->mpdf->AddPage();

      if (!empty($part['subtitle'])) {
         $subtitle = "<p class=\"has-medium-font-size mt-2\">{$part['subtitle']}</p>";
      }

      $content = <<<XHTML
      <div class="valign-center">
         <h1 class="has-large-font-size">{$part['title']}</h1>
         {$subtitle}
      </div>
      XHTML;

      $this->mpdf->WriteHTML($content);
   }

   private function add_section($spine_item)
   {
      $this->mpdf->AddPage();

      $content = '';

      if ($spine_item['show_title'] ?? false && !empty($spine_item['title'])) {
         $content .= "<h1>{$spine_item['title']}</h1>";
      }

      if ($spine_item['show_description'] ?? false && !empty($spine_item['excerpt'])) {
         $content .= "<p class=\"section-description\">{$spine_item['excerpt']}</p>";
      }

      if ($spine_item['show_author'] ?? false && !empty($spine_item['author'])) {
         $content .= "<p class=\"section-author\">{$spine_item['author']}</p>";
      }

      $content .= $spine_item['content'];

      if ($spine_item['show_date'] ?? false) {
         $date_formats = [
            'en' => 'F jS, Y',
            'pt' => 'j \d\e F, Y',
            'es' => 'j \d\e F, Y',
         ];

         $date = date_i18n($date_formats[$this->lang], $spine_item['date'], true);

         $content .= "<p class=\"section-date\">{$date}</p>";
      }

      $this->mpdf->WriteHTML($content);
   }
}
