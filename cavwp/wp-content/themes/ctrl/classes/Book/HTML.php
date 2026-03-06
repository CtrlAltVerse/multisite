<?php

namespace ctrl\Book;

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
      <section>
         <p>{$credits['copyright']}</p>

         <dl>
            {$credits['list']}
         </dl>

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
      $style = $this->get_css();

      $this->content .= <<<HTML
      <!DOCTYPE html>
      <html lang="{$this->lang}">
      <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial-scale=1.0">
         <meta name="robots" content="noindex, nofollow">
         <link rel="preconnect" href="https://fonts.googleapis.com">
         <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
         <link href="https://fonts.googleapis.com/css2?family=Inria+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&family=Jost:ital,wght@0,100..900;1,100..900&family=Lato:ital,wght@0,400;0,700;1,400;1,700&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Source+Serif+4:ital,opsz,wght@0,8..60,200..900;1,8..60,200..900&display=swap" rel="stylesheet">
         <title>{$this->title}, {$this->info['author']}</title>
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
         'content' => $this->get_title(),
      ]);

      // CREDITS
      $this->add_credits();

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
