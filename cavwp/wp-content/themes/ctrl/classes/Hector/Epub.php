<?php

namespace ctrl\Hector;

use ZipArchive;

const EPUB_TEMPLATES = [
   'chapter' => [
      'role' => 'chapter',
      'type' => 'bodymatter',
      'toc'  => true,
   ],
   'cover' => [
      'role' => 'cover',
      'type' => 'cover',
      'toc'  => false,
   ],
   'titlepage' => [
      'type' => 'frontmatter',
      'toc'  => true,
   ],
   'prologue' => [
      'role' => 'prologue',
      'type' => 'bodymatter',
      'toc'  => true,
   ],
   'epilogue' => [
      'role' => 'epilogue',
      'type' => 'bodymatter',
      'toc'  => true,
   ],
   'other-credits' => [
      'role' => 'credits',
      'type' => 'backmatter',
      'toc'  => true,
   ],
   'dedication' => [
      'role' => 'dedication',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   'preface' => [
      'role' => 'preface',
      'type' => 'frontmatter',
      'toc'  => true,
   ],
   'introduction' => [
      'role' => 'introduction',
      'type' => 'frontmatter',
      'toc'  => true,
   ],
   'appendix' => [
      'role' => 'appendix',
      'type' => 'backmatter',
      'toc'  => true,
   ],
   'conclusion' => [
      'role' => 'conclusion',
      'type' => 'backmatter',
      'toc'  => false,
   ],
   'acknowledgments' => [
      'role' => 'acknowledgments',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   'foreword' => [
      'role' => 'foreword',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   'preamble' => [
      'role' => 'preamble',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   'division' => [
      'role' => 'part',
      'type' => 'bodymatter',
      'toc'  => false,
   ],
   'afterword' => [
      'role' => 'afterword',
      'type' => 'backmatter',
      'toc'  => false,
   ],
   'epigraph' => [
      'role' => 'epigraph',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   'glossary' => [
      'role' => 'glossary',
      'type' => 'backmatter',
      'toc'  => true,
   ],
   'colophon' => [
      'role' => 'colophon',
      'type' => 'backmatter',
      'toc'  => false,
   ],
   'bibliography' => [
      'role' => 'bibliography',
      'type' => 'backmatter',
      'toc'  => true,
   ],
   'bio' => [
      'role' => 'bio',
      'type' => 'backmatter',
      'toc'  => true,
   ],
];

final class Epub extends Book
{
   private $count_chars = 0;
   private $count_words = 0;
   private $folders     = [];
   private $temp_folder;
   private $uuid;

   private function setup()
   {
      $this->type = 'epub';

      $this->temp_folder = HECTOR_FOLDER . 'z_epub_' . $this->info['slug'] . '_' . $this->version;

      if (is_dir($this->temp_folder)) {
         return false;
      }

      $this->uuid = wp_generate_uuid4();

      $this->folders = [
         $this->temp_folder,
         $this->temp_folder . '/META-INF',
         $this->temp_folder . '/OEBPS',
         $this->temp_folder . '/OEBPS/content',
         $this->temp_folder . '/OEBPS/assets',
         $this->temp_folder . '/OEBPS/assets/images',
      ];

      foreach ($this->folders as $folder) {
         $folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);
         mkdir($folder, 0o777);

         if (!is_dir($folder)) {
            return debug("Cannot create folder: {$folder}");
         }
      }

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

   protected function save()
   {
      if (!extension_loaded('zip')) {
         return debug('zip extension is not loaded');
      }

      $file_name = Utils::get_filename($this->info['ID'], $this->version) . '.epub';

      $zip = new ZipArchive();

      if (!$zip->open(HECTOR_FOLDER . $file_name, ZipArchive::CREATE)) {
         return debug('Failed to create zip file.');
      }

      foreach ($this->folders as $folder) {
         $files = scandir($folder);

         foreach ($files as $file) {
            if ('.' === $file || '..' === $file) {
               continue;
            }

            $full_path = $folder . '/' . $file;

            if (is_dir($full_path)) {
               continue;
            }

            $checks = explode('.', $file);

            if (empty($checks[1]) && 'mimetype' !== $file) {
               continue;
            }

            $zip->addFile($full_path, str_replace($this->temp_folder . '/', '', $full_path));

            $files_to_delete[] = $full_path;
         }
      }

      $zip->close();

      foreach ($files_to_delete as $delete) {
         unlink($delete);
      }

      $folders = array_reverse($this->folders);

      foreach ($folders as $folder) {
         rmdir($folder);
      }

      return $file_name;
   }

   private function add_container()
   {
      $container = <<<'XML'
      <?xml version="1.0" encoding="UTF-8"?>
      <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
          <rootfiles>
              <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
         </rootfiles>
      </container>
      XML;

      $this->create_file('/META-INF/container.xml', $container);
   }

   private function add_cover()
   {
      $content = <<<'HTML'
      <figure class="m-0 has-text-align-center is-style-portrait" id="cover">
         <img role="doc-cover" src="../assets/images/cover.jpg" alt="" />
      </figure>
      HTML;

      $this->add_section('000', [
         'section_type' => 'cover',
         'content'      => $content,
      ], false, false);
   }

   private function add_credits()
   {
      $credits = $this->get_credits();

      if (!empty($this->info['versions'][$this->version]['isbn'])) {
         $credits['list'] .= <<<HTML
            <dt>ISBN</dt>
            <dd>{$this->info['versions'][$this->version]['isbn']}</dd>
         HTML;
      }

      $content = <<<HTML
      <figure class="mt-0 mb-8 has-black-sky-background-color has-text-align-center">
         <a href="{$this->site_link}" target="_blank">
            <img src="../assets/images/CtrlAltVerso.png" alt="{$this->site_name}" />
         </a>
      </figure>
      <section epub:type="copyright-page" id="copyright-page">
         <dl>
            {$credits['list']}

            <dt>{$this->site_name} • <a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></dt>
            <dd>{$credits['copyright']}</dd>
         </dl>
      </section>
      HTML;

      $this->add_section('002', [
         'section_type' => 'other-credits',
         'content'      => $content,
      ], false);
   }

   private function add_division($key, $part)
   {
      $this->add_section($key, [
         'section_type' => 'division',
         'content'      => $this->get_division($part),
      ], false);
   }

   private function add_nav()
   {
      $nav_itens = '';

      // MAKE NAV SUMMARY
      foreach ($this->info['parts'] as $part_key => $part) {
         $division_key = '';

         if ($this->is_multipart && !empty($part['title'])) {
            $division_key .= str_pad($part_key + 1, 3, '0', STR_PAD_RIGHT);

            $nav_itens .= <<<HTML
            <li>
               <a href="content/{$division_key}-division.xhtml">{$part['title']}</a>
               <ol>
            HTML;
         }

         foreach ($part['spine'] as $key => $spine_item) {
            if (empty($spine_item['show']['toc'])) {
               continue;
            }

            $section_key = $division_key . str_pad($key + 1, 3, '0', STR_PAD_LEFT);

            $nav_itens .= <<<HTML
            <li>
               <a href="content/{$section_key}-{$spine_item['section_type']}.xhtml">{$spine_item['title']}</a>
            </li>
            HTML;
         }

         if ($this->is_multipart && !empty($part['title'])) {
            $nav_itens .= <<<'HTML'
            </ol>
            </li>
            HTML;
         }
      }

      $nav = <<<HTML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <link href="assets/hector.css" type="text/css" rel="stylesheet" />
         <title>{$this->title}</title>
      </head>
      <body xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="frontmatter">
         <nav epub:type="toc" role="doc-toc" id="toc" class="nav-toc">
            <h1>{$this->title_nav}</h1>
            <ol>
               <li>
                  <a href="content/001-titlepage.xhtml">{$this->title}</a>
               </li>
               {$nav_itens}
               <li>
                  <a href="content/997-bio.xhtml">{$this->title_bio}</a>
               </li>
            </ol>
         </nav>
      </body>
      </html>
      HTML;

      $this->create_file('/OEBPS/nav.xhtml', $nav);
   }

   private function add_ncx()
   {
      $toc_itens = '';

      // MAKE NCX SUMMARY
      foreach ($this->info['parts'] as $part_key => $part) {
         $division_key = '';

         if ($this->is_multipart && !empty($part['title'])) {
            $division_key .= str_pad($part_key + 1, 3, '0', STR_PAD_RIGHT);

            $toc_itens .= <<<XML
            <navPoint id="part-{$division_key}">
               <navLabel>
                  <text>{$part['title']}</text>
               </navLabel>
               <content src="content/{$division_key}-division.xhtml" />
            XML;
         }

         foreach ($part['spine'] as $key => $spine_item) {
            if (empty($spine_item['show']['toc'])) {
               continue;
            }

            $section_key = $division_key . str_pad($key + 1, 3, '0', STR_PAD_LEFT);

            $toc_itens .= <<<XML
            <navPoint id="spine-{$section_key}">
               <navLabel>
                  <text>{$spine_item['title']}</text>
               </navLabel>
               <content src="content/{$section_key}-{$spine_item['section_type']}.xhtml" />
            </navPoint>
            XML;
         }

         if ($this->is_multipart && !empty($part['title'])) {
            $toc_itens .= <<<'XML'
            </navPoint>
            XML;
         }
      }

      $toc = <<<XML
      <?xml version="1.0" encoding="UTF-8" standalone="no"?>
      <ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
         <head>
            <meta content="urn:uuid:{$this->uuid}" name="dtb:uid" />
            <meta content="1" name="dtb:depth" />
            <meta name="dtb:totalPageCount" content="0" />
            <meta name="dtb:maxPageNumber" content="0" />
         </head>
         <docTitle>
            <text>{$this->title}</text>
         </docTitle>
         <navMap>

            <navPoint id="title">
               <navLabel>
                  <text>{$this->title}</text>
               </navLabel>
               <content src="content/001-titlepage.xhtml" />
            </navPoint>

            {$toc_itens}

            <navPoint id="bio">
               <navLabel>
                  <text>{$this->title_bio}</text>
               </navLabel>
               <content src="content/997-bio.xhtml" />
            </navPoint>
         </navMap>
      </ncx>
      XML;

      $this->create_file('/OEBPS/toc.ncx', $toc);
   }

   private function add_opf()
   {
      $release  = date('Y-m-d\TH:i:s\Z', strtotime($this->info['release']));
      $modified = date('Y-m-d\TH:i:s\Z', time());

      $isbn = '';

      if (!empty($this->info['versions'][$this->version]['isbn'])) {
         $isbn .= <<<XML
         <meta refines="#BookId" property="identifier-type" scheme="onix:codelist5">{$this->info['versions'][$this->version]['isbn']}</meta>
         XML;
      }

      $others_titles = '';

      if (!empty($this->info['subtitle'])) {
         $others_titles .= <<<XML
            <dc:title id="t2">{$this->info['subtitle']}</dc:title>
            <meta property="title-type" refines="#t2">subtitle</meta>
         XML;
      }

      if (!empty($this->info['short_title'])) {
         $others_titles .= <<<XML
            <dc:title id="t3">{$this->info['short_title']}</dc:title>
            <meta property="title-type" refines="#t3">short</meta>
         XML;
      }

      if (!empty($this->info['edition_title'])) {
         $others_titles .= <<<XML
            <dc:title id="t4">{$this->info['edition_title']}</dc:title>
            <meta property="title-type" refines="#t4">edition</meta>
         XML;
      }

      $subjects = '';

      if (!empty($this->info['tags'])) {
         foreach ($this->info['tags'] as $tag_ID) {
            $tag = get_term($tag_ID, 'product_tag')->slug;
            $subjects .= "<dc:subject>{$tag}</dc:subject>";
         }
      }

      if (!empty($this->info['attributes']['tipo'])) {
         $type = $this->info['attributes']['tipo'];
         $subjects .= "<dc:subject>{$type}</dc:subject>";
      }

      $series = '';

      if (!empty($this->info['series'])) {
         $series .= <<<XML
            <dc:title id="tc1">{$this->info['series']['title']}</dc:title>
            <meta property="title-type" refines="#tc1">collection</meta>

         	<meta property="belongs-to-collection" id="c02">{$this->info['series']['title']}</meta>
            <meta refines="#c02" property="collection-type">{$this->info['series']['type']}</meta>
            <meta refines="#c02" property="dcterms:identifier">{$this->info['series']['uuid']}</meta>
         XML;

         if (!empty($this->info['series']['position'])) {
            $series .= <<<XML
            <meta refines="#c02" property="group-position">{$this->info['series']['position']}</meta>
            XML;
         }
      }

      $authors = '';

      $manifest_itens = '';
      $spine_itens    = '';

      foreach ($this->info['authors'] as $author_ID => $author) {
         $key = $author_ID + 1;

         $name_invert = Utils::invert_name($author['name']);

         $authors .= <<<XML
            <dc:creator id="creator{$key}">{$author['name']}</dc:creator>
            <meta scheme="marc:relators" property="role" refines="#creator{$key}">aut</meta>
            <meta property="file-as" refines="#creator{$key}">{$name_invert}</meta>
            <meta refines="#creator{$key}" property="display-seq">{$key}</meta>

         XML;
      }

      $contributors = '';

      foreach ($this->info['contributors'] as $key => $contributor) {
         $key++;

         $name_invert = Utils::invert_name($contributor['name']);

         $contributors .= <<<XML
         <dc:contributor id="contrib{$key}">{$contributor['name']}</dc:contributor>
         <meta scheme="marc:relators" property="role" refines="#contrib{$key}">{$contributor['role']}</meta>
         <meta property="file-as" refines="#contrib{$key}">{$name_invert}</meta>

         XML;
      }

      // ADD XHTML FILES TO MANIFEST AND SPINE
      foreach ($this->info['parts'] as $part_key => $part) {
         $division_key = '';

         if ($this->is_multipart && !empty($part['title'])) {
            $division_key .= str_pad($part_key + 1, 3, '0', STR_PAD_RIGHT);

            $manifest_itens .= <<<XML
            <item href="content/{$division_key}-division.xhtml" id="division-{$division_key}" media-type="application/xhtml+xml" />

            XML;

            $spine_itens .= <<<XML
            <itemref idref="division-{$division_key}" />

            XML;
         }

         foreach ($part['spine'] as $key => $spine_item) {
            $section_key = $division_key . str_pad($key + 1, 3, '0', STR_PAD_LEFT);

            $manifest_itens .= <<<XML
               <item href="content/{$section_key}-{$spine_item['section_type']}.xhtml" id="xhtml-{$section_key}-{$spine_item['section_type']}" media-type="application/xhtml+xml" />

            XML;

            $spine_itens .= <<<XML
               <itemref idref="xhtml-{$section_key}-{$spine_item['section_type']}" />

            XML;
         }
      }

      if (!empty($this->images)) {
         foreach ($this->images as $image) {
            $image_name = basename($image['path']);
            $cover      = $image['cover'] ? 'properties="cover-image"' : '';
            $path       = str_replace('../', '', $image['path']);

            $manifest_itens .= <<<XML
               <item href="{$path}" id="{$image_name}" media-type="{$image['type']}" {$cover} />

            XML;
         }
      }

      $description = '';

      if (!empty($this->info['description'])) {
         $description = '<dc:description>' . strip_tags($this->info['description']) . '</dc:description>';
      }

      $opf = <<<XML
      <?xml version="1.0" encoding="utf-8" standalone="no"?>
      <package
         prefix="rendition: http://www.idpf.org/vocab/rendition/# schema: http://schema.org/ ibooks: http://vocabulary.itunes.apple.com/rdf/ibooks/vocabulary-extensions-1.0/ a11y: http://www.idpf.org/epub/vocab/package/a11y/#"
         xmlns="http://www.idpf.org/2007/opf" version="3.0" unique-identifier="BookId">
         <metadata xmlns:opf="http://www.idpf.org/2007/opf" xmlns:dcterms="http://purl.org/dc/terms/"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:ibooks="http://apple.com/ibooks/html-extensions">

            <dc:identifier id="BookId">urn:uuid:{$this->uuid}</dc:identifier>
            {$isbn}

            <meta property="dcterms:issued">{$release}</meta>
            <meta property="dcterms:modified">{$modified}</meta>

            <dc:language>{$this->lang}</dc:language>

            <dc:title id="t1">{$this->info['title']}</dc:title>
            <meta property="title-type" refines="#t1">main</meta>
            {$others_titles}

            {$series}

            {$authors}

            <dc:publisher>{$this->site_name}</dc:publisher>
            <dc:rights>(c) {$this->year} {$this->info['author']}</dc:rights>
            {$description}

            {$contributors}

            {$subjects}

            <meta name="cover" content="cover.jpg" />

            <meta property="rendition:layout">reflowable</meta>
      		<meta property="rendition:flow">auto</meta>
      		<meta property="rendition:orientation">auto</meta>
      		<meta property="rendition:spread">auto</meta>

            <meta property="ibooks:version">1.0</meta>
            <meta property="ibooks:specified-fonts">false</meta>

            <meta property="schema:accessibilityFeature">displayTransformability</meta>
            <meta property="schema:accessibilityFeature">readingOrder</meta>
            <meta property="schema:accessibilityFeature">structuralNavigation</meta>

            <meta property="schema:accessibilityFeature">alternativeText</meta>
            <!-- <meta property="schema:accessibilityFeature">ChemML</meta> -->
            <!-- <meta property="schema:accessibilityFeature">MathML</meta> -->
            <!-- <meta property="schema:accessibilityFeature">rubyAnnotations</meta> -->
            <!-- <meta property="schema:accessibilityFeature">ttsMarkup</meta> -->

            <meta property="schema:accessibilityHazard">noFlashingHazard</meta>
            <meta property="schema:accessibilityHazard">noSoundHazard</meta>
            <meta property="schema:accessibilityHazard">noMotionSimulationHazard</meta>

            <meta property="schema:accessMode">textual</meta>
            <meta property="schema:accessMode">visual</meta>

            <meta property="schema:accessModeSufficient">textual,visual</meta>
            <meta property="schema:accessModeSufficient">textual</meta>

            <meta property="schema:accessibilitySummary">This publication is a reflowable EPUB 3 with structured headings and a logical reading order. All images include alternative text. No audio or video content is included.</meta>

            <meta property="schema:accessibilityAPI">ARIA</meta>
         </metadata>

         <manifest>
            <item href="toc.ncx" id="ncx" media-type="application/x-dtbncx+xml" />
            <item
               href="nav.xhtml" id="navid" media-type="application/xhtml+xml"
               properties="nav" />
            <item href="assets/hector.css" id="hector.css" media-type="text/css" />

            <item href="content/000-cover.xhtml"
               id="xhtml-000-cover"
               media-type="application/xhtml+xml" />
            <item href="content/001-titlepage.xhtml"
               id="xhtml-001-titlepage"
               media-type="application/xhtml+xml" />
             <item href="content/002-other-credits.xhtml"
               id="xhtml-002-other-credits"
               media-type="application/xhtml+xml" />
            <item href="content/997-bio.xhtml"
               id="xhtml-997-bio"
               media-type="application/xhtml+xml" />
            <item href="content/998-acknowledgments.xhtml"
               id="xhtml-998-acknowledgments"
               media-type="application/xhtml+xml" />
            <item href="content/999-colophon.xhtml"
               id="xhtml-999-colophon"
               media-type="application/xhtml+xml" />

            {$manifest_itens}
         </manifest>

         <spine toc="ncx">
            <itemref idref="xhtml-001-titlepage" />
            <itemref idref="xhtml-002-other-credits" />
            {$spine_itens}
            <itemref idref="xhtml-997-bio" />
            <itemref idref="xhtml-998-acknowledgments" />
            <itemref idref="xhtml-999-colophon" />
         </spine>
      </package>
      XML;

      $this->create_file('/OEBPS/content.opf', $opf);
   }

   private function add_section($key, $spine_item, $apply_filter = true, $with_section = true)
   {
      $section_type = $spine_item['section_type'];
      $section      = EPUB_TEMPLATES[$section_type];

      $spine_item['body_type']    = $section['type'];
      $spine_item['section_role'] = $section['role'] ?? false;

      if ('epigraph' === $section_type) {
         $with_section = false;
      }

      if ($apply_filter && !empty($this->images)) {
         foreach ($this->images as $new_image) {
            $spine_item['content'] = str_replace(
               $new_image['old'],
               $new_image['path'],
               $spine_item['content'],
            );
         }
      }

      $content = $this->get_section($spine_item, $with_section, $apply_filter);

      $title = $spine_item['title'] ?? false;

      if (empty($title)) {
         $title = $this->title;
      }

      $template = <<<XML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns:epub="http://www.idpf.org/2007/ops" xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <title>{$title}</title>
         <link href="../assets/hector.css" type="text/css" rel="stylesheet" />
      </head>

      <body xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="{$spine_item['body_type']}">
         {$content}
      </body>
      </html>
      XML;

      $this->create_file("/OEBPS/content/{$key}-{$section_type}.xhtml", $template);
   }

   private function content()
   {
      // MIMETYPE
      $this->create_file('/mimetype', 'application/epub+zip');

      // CSS
      $style = $this->get_css();
      $this->create_file('/OEBPS/assets/hector.css', $style);

      // EPUB STUFF
      $this->search_images();
      $this->add_container();
      $this->add_opf();
      $this->add_nav();
      $this->add_ncx();

      // COVER
      $this->add_cover();

      // TITLE
      $this->add_section('001', [
         'section_type' => 'titlepage',
         'content'      => $this->get_title(),
      ], false);

      // CREDITS
      $this->add_credits();

      // ADD CONTENT SECTIONS
      foreach ($this->info['parts'] as $part_key => $part) {
         $division_key = '';

         if ($this->is_multipart && !empty($part['title'])) {
            $division_key .= str_pad($part_key + 1, 3, '0', STR_PAD_RIGHT);
            $this->add_division($division_key, $part);
         }

         foreach ($part['spine'] as $key => $spine_item) {
            $section_key = $division_key . str_pad($key + 1, 3, '0', STR_PAD_LEFT);
            $this->add_section($section_key, $spine_item);
         }
      }

      // BIO
      $this->add_section(997, [
         'title'        => $this->title_bio,
         'section_type' => 'bio',
         'content'      => $this->get_bio(false),
      ], false, false);

      // CTA
      $this->add_section(998, [
         'title'        => $this->title_cta,
         'section_type' => 'acknowledgments',
         'content'      => $this->get_cta(),
      ], false);

      // COLOPHON
      $this->add_section(999, [
         'section_type' => 'colophon',
         'content'      => $this->get_colophon(),
      ], false);

      if ('amazon' === $this->version) {
         update_post_meta($this->info['ID'], 'chars_count', $this->count_chars);
         update_post_meta($this->info['ID'], 'words_count', $this->count_words);
      }
   }

   private function create_file($file, $content)
   {
      if (str_ends_with($file, '.xhtml') && 'amazon' === $this->version) {
         $text = trim(strip_tags($content));

         $this->count_chars += strlen($text);
         $this->count_words += str_word_count($text);
         unset($text);
      }

      $handle = fopen($this->temp_folder . $file, 'w+');
      fwrite($handle, $content);
      fclose($handle);
   }

   private function download_image($url, $new_filename = null, $is_cover = false, $add_extension = false)
   {
      $image_name = is_null($new_filename) ? basename($url) : $new_filename;

      $images_path = $this->temp_folder . '/OEBPS/assets/images/';

      if ($add_extension) {
         $image_type = @getimagesize($url);

         if (false === $image_type) {
            return debug('Cannot get image type: ' . $url);
         }

         $image_name .= image_type_to_extension($image_type[2]);
      }

      if (!@copy($url, $images_path . $image_name)) {
         return debug('Cannot copy ' . $url);
      }

      if (empty($image_type)) {
         $image_type = getimagesize($images_path . $image_name);
      }

      if (false === $image_type) {
         return debug('Cannot get image type: ' . $url);
      }

      $image_type = $image_type['mime'];

      $this->images[$image_name] = [
         'old'   => $url,
         'path'  => "../assets/images/{$image_name}",
         'type'  => $image_type,
         'cover' => $is_cover,
      ];
   }

   private function search_images()
   {
      if (!empty($this->info['cover'])) {
         $this->download_image($this->info['cover'], 'cover.jpg', true);
      }

      $asterism = \get_field('asterism', 'options');

      if (!empty($asterism)) {
         $this->download_image(wp_get_attachment_image_url($asterism, 'full'), 'asterism.png');
      }

      $logo = \get_field('logo', 'options');

      if (!empty($logo)) {
         $this->download_image(wp_get_attachment_image_url($logo, 'full'), 'CtrlAltVerso.png');
      }

      if (!empty($this->info['authors'])) {
         foreach ($this->info['authors'] as $author_ID => $_author) {
            $avatar = get_avatar_url($author_ID, ['size' => 180]);
            $this->download_image($avatar, 'avatar-' . $author_ID, add_extension: true);
         }
      }

      // SEARCH FOR IMAGENS IN THE CONTENT
      foreach ($this->info['parts'] as $part) {
         foreach ($part['spine'] as $spine_item) {
            $found_images = preg_match_all(
               '/(\<img.*?src=[\'|"])(.*?)([\'|"].*?\>)/mis',
               $spine_item['content'],
               $image_matches,
            );

            if (empty($found_images)) {
               continue;
            }

            if (empty($image_matches[2])) {
               continue;
            }

            foreach ($image_matches[2] as $url) {
               if (is_environment('production')) {
                  $url = str_replace($this->site_link . '/wp-content/uploads', 'https://cdn.altvers.net', $url);
               }

               $image_name = basename($url);

               if (in_array($image_name, array_keys($this->images))) {
                  continue;
               }

               $this->download_image($url);
            }
         }
      }
   }
}
