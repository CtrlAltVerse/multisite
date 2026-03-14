<?php

namespace ctrl\Hector;

class HTML extends Book
{
   private $content = '';

   private function setup()
   {
      $this->type = 'html';

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
      <div>
         <p>{$credits['copyright']}</p>

         <dl>
            {$credits['list']}
         </dl>
      </div>

      {$credits['table']}
      HTML;

      $this->add_section([
         'layout'  => ['page-clean', 'page-between', 'break-before-always'],
         'content' => $content,
      ]);
   }

   private function add_division($part)
   {
      $this->add_section([
         'layout'  => ['page-clean', 'page-center', 'break-before-always'],
         'content' => $this->get_division($part),
      ]);
   }

   private function add_footer()
   {
      $this->content .= <<<'HTML'
         </main>
         <script>
            const params = new URLSearchParams(location.search);
            if(params.has('isbn')){
               const mask = '###-#-########-#';
               let isbn = params.get('isbn');
               let i = 0;
               document.querySelector('.isbn').textContent =
                  'ISBN: ' + mask.split('').map((m) => '#'===m ? isbn[i++] : m).join('');
            }
         </script>
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
      HTML;
      $this->content .= Utils::add_fonts_google(false);
      $this->content .= <<<HTML
         <title>{$this->title}</title>
         <style type="text/css">
            {$style}
         </style>
      </head>
      <body>
         <main>
      HTML;
   }

   private function add_section($snipe_item)
   {
      $this->content .= $this->get_section($snipe_item);
   }

   private function content()
   {
      $this->add_header();

      // TITLE
      $this->add_section([
         'layout'  => ['page-clean', 'page-center'],
         'content' => $this->get_title(false),
      ]);

      // CREDITS
      $this->add_credits();

      // FACE
      $this->add_section([
         'layout'  => ['page-clean', 'page-between', 'break-before-always', 'blank-before'],
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
         'layout'  => ['page-clean', 'break-before-right'],
         'title'   => $this->title_bio,
         'content' => $this->get_bio(false),
      ]);

      // CTA
      $this->add_section([
         'layout'  => ['page-clean', 'break-before-always'],
         'title'   => $this->title_cta,
         'content' => $this->get_cta('CtrlAltVerso'),
      ]);

      // COLOPHON
      $this->add_section([
         'layout'  => ['page-clean', 'break-before-left', 'page-bottom'],
         'content' => $this->get_colophon(),
      ]);

      $this->add_footer();
   }

   private function save()
   {
      $filename = Utils::get_filename($this->info['ID'], $this->version) . '.html';

      $handle = fopen(HECTOR_FOLDER . $filename, 'w+');
      fwrite($handle, $this->content);
      fclose($handle);
   }
}
