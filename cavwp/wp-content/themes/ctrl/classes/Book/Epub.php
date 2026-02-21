<?php

namespace ctrl\Book;

use cavWP\Utils as CavWPUtils;
use WP_Theme_JSON_Resolver;
use ZipArchive;

define('EPUB_TEMPLATES', [
   'chapter' => [
      'role' => 'chapter',
      'type' => 'bodymatter',
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
      'role' => 'division',
      'type' => 'bodymatter',
      'toc'  => false,
   ],
   'afterword' => [
      'role' => 'afterword',
      'type' => 'backmatter',
      'toc'  => false,
   ],
   // blockquote p cite
   'epigraph' => [
      'role' => 'epigraph',
      'type' => 'frontmatter',
      'toc'  => false,
   ],
   // dl dt dd
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
   // ul li
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
]);
define('LOCALES', [
   'en' => 'en_US',
   'pt' => 'pt_BR',
   'es' => 'es_ES',
]);

final class Epub
{
   private $folders = [];
   private $images  = [];
   private $info;
   private $is_multipart;
   private $lang;
   private $site_domain;
   private $site_link;
   private $site_name;
   private $temp_folder;
   private $title;
   private $title_bio;
   private $uuid;
   private $version;
   private $year;

   public function __construct($version, $info)
   {
      $this->info         = $info;
      $this->title        = $info['title'];
      $this->lang         = $info['attributes']['lang'];
      $this->version      = $version;
      $this->year         = $info['release']->date('Y');
      $this->title_bio    = _n('Sobre o autor', 'Sobre os autores', count($this->info['authors']), 'ctrl');
      $this->is_multipart = count($this->info['parts']) > 1;

      $this->site_name   = get_bloginfo('name');
      $this->site_link   = home_url();
      $this->site_domain = CavWPUtils::clean_domain($this->site_link);

      $this->uuid = wp_generate_uuid4();

      $this->info['contributors'][] = [
         'name' => 'CtrlAltVerso',
         'role' => 'pbl',
      ];

      $this->temp_folder = HECTOR_FOLDER . 'epub_' . $info['slug'] . '_' . $version;

      $this->folders = [
         $this->temp_folder,
         $this->temp_folder . '/META-INF',
         $this->temp_folder . '/OEBPS',
         $this->temp_folder . '/OEBPS/content',
         $this->temp_folder . '/OEBPS/assets',
         $this->temp_folder . '/OEBPS/assets/images',
      ];
   }

   public function create()
   {
      switch_to_locale(LOCALES[$this->lang]);

      if (is_dir($this->temp_folder)) {
         return;
      }

      foreach ($this->folders as $folder) {
         $folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);
         mkdir($folder, 0o777);

         if (!is_dir($folder)) {
            return debug("Cannot create folder: {$folder}");
         }
      }

      $this->create_file('/mimetype', 'application/epub+zip');
      $this->add_css();
      $this->add_images();
      $this->add_container();
      $this->add_opf();
      $this->add_nav();
      $this->add_ncx();
      $this->add_cover();
      $this->add_title();
      $this->add_credits();

      // ADD CONTENT SECTIONS
      foreach ($this->info['parts'] as $part_key => $part) {
         if ($this->is_multipart) {
            $this->add_division($part_key, $part);
         }

         foreach ($part['spine'] as $key => $spine_item) {
            $key = str_pad($key + 3, 3, '0', STR_PAD_LEFT);
            $this->add_section($key, $spine_item);
         }
      }

      $this->add_bio();
      $this->add_cta();
      $this->add_colophon();
      restore_previous_locale();

      return $this->zip();
   }

   private function add_bio()
   {
      $content = '';

      foreach ($this->info['authors'] as $author_ID => $author) {
         $img = '../assets/images/avatar-' . $author_ID . '.jpg';

         $links = '';

         if (!empty($author['link'])) {
            $site_text = esc_html__('Site pessoal', 'ctrl');
            $links .= "<li><a href=\"{$author['link']}\" target=\"_blank\">{$site_text}</a></li>";
         }

         if ('amazon' === $this->version && !empty($author['amazon-profile'])) {
            $profile_text = esc_html__('Perfil na Amazon', 'ctrl');
            $links .= "<li><a href=\"{$author['amazon-profile']}\" target=\"_blank\">{$profile_text}</a></li>";
         }

         $content = <<<XML
         <section class="break-inside-avoid" epub:type="bio" role="doc-credit" id="bio-{$author_ID}">
            <img src="{$img}" alt="" class="rounded" />
            <h2>{$author['name']}</h2>
            <p class="has-text-align-left">{$author['bio']}</p>
            <ul>
               {$links}
            </ul>
         </section>
         <hr class="is-style-transition" />
         XML;
      }

      $this->add_section(997, [
         'show_title'   => true,
         'title'        => $this->title_bio,
         'section_type' => 'bio',
         'content'      => $content,
      ], false, false);
   }

   private function add_colophon()
   {
      $title      = mb_strtoupper(esc_html__('Uma publicação', 'ctrl'));
      $site_links = get_field('links', 'options')[0]['group'];

      $links = '';

      if (!empty($site_links)) {
         foreach ($site_links as $site_link) {
            $link_domain = CavWPUtils::clean_domain($site_link['link']);

            $links .= <<<XML
               <li><a href="{$site_link['link']}" target="_blank">{$link_domain}</a></li>
            XML;
         }
      }

      $content = <<<XML
      <div class="valign-bottom">
      <p class="has-medium-font-size has-text-align-center"><strong>{$title}</strong></p>
      <div class="p-1 has-white-background-color has-text-align-center">
         <a href="{$this->site_link}" target="_blank">
            <img src="../assets/images/ctrlaltverso-dark.jpg" alt="{$this->site_name}" />
         </a>
      </div>
      <ul class="list-none has-text-align-center">
         <li><a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></li>
         {$links}
      </ul>
      </div>
      XML;

      $this->add_section(999, [
         'section_type' => 'colophon',
         'content'      => $content,
      ], false);
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
      $content = <<<XML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns:epub="http://www.idpf.org/2007/ops" xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <title>{$this->title}</title>
         <link href="../assets/blitz.css" type="text/css" rel="stylesheet" />
      </head>

      <body class="h-100" xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="cover">
         <figure class="h-100 m-0 has-text-align-center is-style-portrait" id="cover">
            <img role="doc-cover" src="../assets/images/cover.jpg" alt="" />
         </figure>
      </body>
      </html>
      XML;

      $this->create_file('/OEBPS/content/000-cover.xhtml', $content);
   }

   private function add_credits()
   {
      $list = '';

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
         foreach ($this->info['contributors'] as $contributor) {
            if (in_array($contributor['role'], array_keys($this->info['contributors']))) {
               $contributors[$contributor['role']][] = $contributor['name'];
            } else {
               $contributors[$contributor['role']] = [$contributor['name']];
            }
         }

         foreach ($contributors as $role => $contributors_names) {
            $role  = Utils::get_roles($role);
            $names = CavWPUtils::parse_titles($contributors_names);

            $list .= <<<XML
               <dt>{$role}</dt>
               <dd>{$names}</dd>
            XML;
         }
      }

      if (!empty($this->info['isbn'])) {
         $list .= <<<XML
         <dt>ISBN</dt>
         <dd>{$this->info['isbn']}</dd>
         XML;
      }

      $all_rights = esc_attr__('Todos os direitos reservados.', 'ctrl');
      $author     = rtrim($this->info['author'], '.');

      $credits = <<<XML
      <div class="p-1 has-white-background-color has-text-align-center">
         <a href="{$this->site_link}" target="_blank">
            <img src="../assets/images/ctrlaltverso-dark.jpg" alt="{$this->site_name}" />
         </a>
      </div>
      <hr class="is-style-transition" />
      <section epub:type="copyright-page" id="copyright-page">
      <dl>
         <dt>{$this->title}</dt>
         <dd>{$this->info['author']}</dd>

         {$list}

         <dt>{$this->site_name} • <a href="{$this->site_link}" target="_blank">{$this->site_domain}</a></dt>
         <dd>© {$this->year} {$author}. {$all_rights}</dd>
      </dl>
      </section>
      XML;

      $this->add_section('002', [
         'section_type' => 'other-credits',
         'content'      => $credits,
      ], false);
   }

   private function add_css()
   {
      $css = get_option('cav_hector_epub_style', '');

      $settings = WP_Theme_JSON_Resolver::get_merged_data()->get_settings();
      $colors   = array_merge($settings['color']['palette']['default'], $settings['color']['palette']['theme'] ?? []);

      if (!empty($colors)) {
         foreach ($colors as $color) {
            $css .= <<<CSS
            .has-{$color['slug']}-color {
               color: {$color['color']};
            }
            .has-{$color['slug']}-background-color {
               background-color: {$color['color']};
            }
            CSS;
         }
      }

      $this->create_file('/OEBPS/assets/blitz.css', $css);
   }

   private function add_cta()
   {
      $link = '';

      if (!empty($this->info['links'])) {
         foreach ($this->info['links'] as $store_link) {
            if (str_contains($store_link, $this->version)) {
               break;
            }
         }

         $link_text = sprintf(
            esc_html__('%s na loja %s', 'ctrl'),
            $this->title,
            ucfirst($this->version),
         );

         $link = "<p class=\"has-text-align-justify mt-2\"><a href=\"{$store_link}\" target=\"_blank\">{$link_text}</a></p>";
      }

      // Thank you for your purchase and for reading this book. It means a great deal to us. If possible, please consider leaving a rating and a review at the store where you purchased it.
      $line1 = esc_html__('Agradecemos sua compra e principalmente pela leitura deste livro. Isto vale muito para nós. ', 'ctrl');
      $line2 = esc_html__('Se puder, deixe sua avaliação e um comentário na loja que comprou.', 'ctrl');

      $content = <<<XML
         <p class="has-text-align-justify">{$line1}</p>
         <p class="has-text-align-justify">{$line2}</p>
         {$link}
      XML;

      $this->add_section(998, [
         'show_title'   => true,
         'title'        => esc_html__('Obrigado', 'ctrl'),
         'section_type' => 'acknowledgments',
         'content'      => $content,
      ], false);
   }

   private function add_division($key, $part)
   {
      $subtitle = '';

      if (!empty($part['subtitle'])) {
         $subtitle = "<p class=\"has-medium-font-size mt-2\">{$part['subtitle']}</p>";
      }

      $content = <<<XHTML
      <div class="valign-center">
         <h1 class="has-large-font-size">{$part['title']}</h1>
         {$subtitle}
      </div>
      XHTML;

      $key = str_pad($key + 1, 2, '0', STR_PAD_LEFT);
      $this->add_section($key, [
         'section_type' => 'division',
         'content'      => $content,
      ], false);
   }

   private function add_images()
   {
      if (!empty($this->info['cover'])) {
         $this->save_image($this->info['cover'], 'cover.jpg', true);
      }

      $asterism = \get_field('asterism', 'options');

      if (!empty($asterism)) {
         $this->save_image(wp_get_attachment_image_url($asterism, 'full'), 'asterism.png');
      }

      $logo = \get_field('logo', 'options');

      if (!empty($logo)) {
         $this->save_image(wp_get_attachment_image_url($logo, 'full'), 'ctrlaltverso-dark.jpg');
      }

      if (!empty($this->info['authors'])) {
         foreach ($this->info['authors'] as $author_ID => $author) {
            $this->save_image($author['avatar'], 'avatar-' . $author_ID . '.jpg');
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
               $image_name = basename($url);

               if (in_array($image_name, array_keys($this->images))) {
                  continue;
               }

               $this->save_image($url);
            }
         }
      }
   }

   private function add_nav()
   {
      $nav_titles = [
         'en' => 'Table of Contents',
         'pt' => 'Sumário',
         'es' => 'Sumario',
      ];

      $nav_itens = '';

      // MAKE NAV SUMMARY
      foreach ($this->info['parts'] as $part_key => $part) {
         if ($this->is_multipart) {
            $part_key = str_pad($part_key + 1, 2, '0', STR_PAD_LEFT);
            $nav_itens .= <<<XML
            <li>
               <a href="content/{$part_key}-division.xhtml">{$part['title']}</a>
               <ol>
            XML;
         }

         foreach ($part['spine'] as $key => $spine_item) {
            if (!$spine_item['show_toc']) {
               continue;
            }

            $key = str_pad($key + 3, 3, '0', STR_PAD_LEFT);

            $nav_itens .= <<<XML
            <li>
               <a href="content/{$key}-{$spine_item['section_type']}.xhtml">{$spine_item['title']}</a>
            </li>
            XML;
         }

         if ($this->is_multipart) {
            $nav_itens .= <<<'XML'
            </ol>
            </li>
            XML;
         }
      }

      $nav = <<<XML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <link href="assets/blitz.css" type="text/css" rel="stylesheet" />
         <title>{$this->title}</title>
      </head>
      <body xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="frontmatter">
         <nav epub:type="toc" role="doc-toc" id="toc" class="nav-toc">
            <h1>{$nav_titles[$this->lang]}</h1>
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
      XML;

      $this->create_file('/OEBPS/nav.xhtml', $nav);
   }

   private function add_ncx()
   {
      $toc_itens = '';

      // MAKE NCX SUMMARY
      foreach ($this->info['parts'] as $part) {
         if ($this->is_multipart) {
            $part_key = str_pad($part_key + 1, 2, '0', STR_PAD_LEFT);
            $nav_itens .= <<<XML
            <navPoint id="part-{$part_key}">
               <navLabel>
                  <text>{$part['title']}</text>
               </navLabel>
               <content src="content/{$part_key}-division.xhtml" />
            XML;
         }

         foreach ($this->info['spine'] as $key => $spine_item) {
            if (!$spine_item['show_toc']) {
               continue;
            }

            $key = str_pad($key + 3, 3, '0', STR_PAD_LEFT);

            $toc_itens .= <<<XML
            <navPoint id="spine-{$key}">
               <navLabel>
                  <text>{$spine_item['title']}</text>
               </navLabel>
               <content src="content/{$key}-{$spine_item['section_type']}.xhtml" />
            </navPoint>
            XML;
         }

         if ($this->is_multipart) {
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
      $release  = $this->info['release']->date('Y-m-d\TH:i:s\Z');
      $modified = date('Y-m-d\TH:i:s\Z', time());

      $isbn = '';

      if (!empty($this->info['isbn'])) {
         $isbn .= <<<XML
         <meta refines="#BookId" property="identifier-type" scheme="onix:codelist5">{$this->info['isbn']}</meta>
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

         $name_invert = $this->invert_name($author['name']);

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

         $name_invert = $this->invert_name($contributor['name']);

         $contributors .= <<<XML
         <dc:contributor id="contrib{$key}">{$contributor['name']}</dc:contributor>
         <meta scheme="marc:relators" property="role" refines="#contrib{$key}">{$contributor['role']}</meta>
         <meta property="file-as" refines="#contrib{$key}">{$name_invert}</meta>

         XML;
      }

      // ADD XHTML FILES TO MANIFEST AND SPINE
      foreach ($this->info['parts'] as $part_key => $part) {
         if ($this->is_multipart) {
            $part_key = str_pad($part_key + 1, 2, '0', STR_PAD_LEFT);

            $manifest_itens .= <<<XML
            <item href="content/{$part_key}-division.xhtml" id="division-{$part_key}" media-type="application/xhtml+xml" />

            XML;

            $spine_itens .= <<<XML
            <itemref idref="division-{$part_key}" />

            XML;
         }

         foreach ($part['spine'] as $key => $spine_item) {
            $key = str_pad($key + 3, 3, '0', STR_PAD_LEFT);

            $manifest_itens .= <<<XML
               <item href="content/{$key}-{$spine_item['section_type']}.xhtml" id="xhtml-{$key}-{$spine_item['section_type']}" media-type="application/xhtml+xml" />

            XML;

            $spine_itens .= <<<XML
               <itemref idref="xhtml-{$key}-{$spine_item['section_type']}" />

            XML;
         }
      }

      if (!empty($this->images)) {
         foreach ($this->images as $image) {
            $image_name = basename($image['path']);
            $cover      = $image['cover'] ? 'properties="cover-image"' : '';

            $manifest_itens .= <<<XML
               <item href="OEBPS/{$image['path']}" id="{$image_name}" media-type="{$image['type']}" {$cover} />

            XML;
         }
      }

      $description = strip_tags($this->info['description']);

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
            <dc:description>{$description}</dc:description>

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
            <item href="assets/blitz.css" id="blitz.css" media-type="text/css" />

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
      $body_type    = $section['type'];
      $section_role = $section['role'];

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

      if ($apply_filter) {
         if (!empty($this->images)) {
            foreach ($this->images as $new_image) {
               $spine_item['content'] = str_replace(
                  $new_image['old'],
                  $new_image['path'],
                  $spine_item['content'],
               );
            }
         }
         $content .= $this->parse_content($spine_item['content']);
      } else {
         $content .= $spine_item['content'];
      }

      if ($spine_item['show_date'] ?? false) {
         $date_formats = [
            'en' => 'F jS, Y',
            'pt' => 'j \d\e F, Y',
            'es' => 'j \d\e F, Y',
         ];

         $date = date_i18n($date_formats[$this->lang], $spine_item['date'], true);

         $content .= "<p class=\"section-date\">{$date}</p>";
      }

      $section_start = '';
      $section_end   = '';

      if ($with_section) {
         $section_start = "<section epub:type=\"{$section_type}\" role=\"doc-{$section_role}\" id=\"{$section_role}\">";

         $section_end = '</section>';
      }

      $template = <<<XML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns:epub="http://www.idpf.org/2007/ops" xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <title>{$this->title}</title>
         <link href="../assets/blitz.css" type="text/css" rel="stylesheet" />
      </head>

      <body xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="{$body_type}">
         {$section_start}
            {$content}
         {$section_end}
      </body>
      </html>
      XML;

      $this->create_file("/OEBPS/content/{$key}-{$section_type}.xhtml", $template);
   }

   private function add_title()
   {
      $subtitle = '';

      if (!empty($this->info['subtitle'])) {
         $subtitle = <<<XML
         <br /><span class="has-medium-font-size" epub:type="subtitle" role="doc-subtitle">{$this->info['subtitle']}</span>
         XML;
      }

      $content = <<<XML
      <?xml version="1.0" encoding="utf-8"?>
      <!DOCTYPE html>

      <html xmlns:epub="http://www.idpf.org/2007/ops" xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$this->lang}" lang="{$this->lang}">
      <head>
         <meta charset="utf-8" />
         <title>{$this->title}</title>
         <link href="../assets/blitz.css" type="text/css" rel="stylesheet" />
      </head>

      <body xml:lang="{$this->lang}" lang="{$this->lang}" epub:type="frontmatter">
         <section class="valign-center" epub:type="titlepage" id="titlepage">
            <h1 class="has-text-align-center" epub:type="fulltitle">
               <span class="has-x-large-font-size" epub:type="title">{$this->title}</span>
               {$subtitle}
            </h1>
            <p class="has-text-align-center has-large-font-size">{$this->info['author']}</p>
            <br />
            <hr class="is-style-transition" />
            <br />
            <p class="has-text-align-center has-medium-font-size">CtrlAltVerso</p>
            <p class="has-text-align-center">{$this->year}</p>
         </section>
      </body>
      </html>
      XML;

      $this->create_file('/OEBPS/content/001-titlepage.xhtml', $content);
   }

   private function create_file($file, $content)
   {
      $handle = fopen($this->temp_folder . $file, 'w+');
      fwrite($handle, $content);
      fclose($handle);
   }

   private function invert_name($name)
   {
      $names = explode(' ', trim($name));
      $last  = array_pop($names);
      $names = implode(' ', $names);

      return "{$last}, {$names}";
   }

   private function parse_content($content)
   {
      $content = preg_replace_callback(
         '/<p(?![^>]*\bclass=)([^>]*)>/i',
         fn($matches) => '<p class="has-text-align-justify"' . $matches[1] . '>',
         $content,
      );

      $content = preg_replace_callback(
         '/<p([^>]*class=")([^"]*)("[^>]*)>/i',
         function($matches) {
            $classes = $matches[2];

            if (preg_match('/\bhas-text-align-[a-z]+\b/i', $classes)) {
               return $matches[0];
            }

            $new_classes = trim($classes . ' has-text-align-justify');

            return '<p' . $matches[1] . $new_classes . $matches[3] . '>';
         },
         $content,
      );

      $content = preg_replace(
         '/\<(script|iframe)[^>]*\>.*?\<\/(script|iframe)\>/mis',
         '',
         $content,
      );

      return str_replace('<br>', '<br/>', $content);
   }

   private function save_image($url, $new_filename = null, $is_cover = false)
   {
      $image_name = is_null($new_filename) ? basename($url) : $new_filename;

      $images_path = $this->temp_folder . '/OEBPS/assets/images/';

      $image_type = @getimagesize($url);

      if (false === $image_type) {
         return debug('Cannot get image type: ' . $url);
      }

      $image_type = $image_type['mime'];

      if (!@copy($url, $images_path . $image_name)) {
         return debug('Cannot copy ' . $url);
      }

      $this->images[$image_name] = [
         'old'   => $url,
         'path'  => "../assets/images/{$image_name}",
         'type'  => $image_type,
         'cover' => $is_cover,
      ];
   }

   private function zip()
   {
      if (!extension_loaded('zip')) {
         return debug('zip extension is not loaded');
      }

      $file_name = Utils::get_filename($this->info['ID'], $this->version);

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
}
