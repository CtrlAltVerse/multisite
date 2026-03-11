<?php

namespace ctrl\Hector;

class HTML extends Book
{
   private $content = '';

   private function setup()
   {
      return is_dir(HECTOR_FOLDER);
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

      $content = <<<HTML
      <section class="page-between">
         <div>
            <p>{$credits['copyright']}</p>

            <dl>
               {$credits['list']}
            </dl>
         </div>

         {$credits['table']}
      </section>
      HTML;

      $this->add_section([
         'content' => $content,
      ], false);
   }

   private function add_division($part)
   {
      $this->add_section([
         'content' => $this->get_division($part),
      ]);
   }

   private function add_footer()
   {
      $this->content .= <<<'HTML'
         </main>
      </body>
      </html>
      HTML;
   }

   private function add_header()
   {
      $style = $this->get_css('html');

      $this->content .= <<<HTML
      <!DOCTYPE html>
      <html lang="{$this->lang}">
      <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <meta name="robots" content="noindex, nofollow">
      HTML;
      $this->content .= Utils::add_fonts_google(false);
      $this->content .= <<<HTML
         <title>{$this->title} — {$this->info['author']}</title>
         <style type="text/css">
            {$style}
         </style>
      </head>
      <body>
         <main>
      HTML;
   }

   private function add_section($snipe_item, $with_section = true, $apply_filter = true)
   {
      $this->content .= '<div class="break-before-always"></div>';
      $this->content .= $this->get_section($snipe_item, $with_section, $apply_filter);
   }

   private function content()
   {
      $this->add_header();

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
         'content' => $this->get_bio(false),
      ], false);

      // CTA
      $this->add_section([
         'title'   => $this->title_cta,
         'content' => $this->get_cta('CtrlAltVerso'),
      ]);

      // COLOPHON
      $this->add_section([
         'content' => $this->get_colophon(),
      ]);

      $this->add_footer();
   }

   private function save()
   {
      $filename = Utils::get_filename($this->info['ID'], 'print') . '.html';

      $handle = fopen(HECTOR_FOLDER . $filename, 'w+');
      fwrite($handle, $this->content);
      fclose($handle);
   }
}
