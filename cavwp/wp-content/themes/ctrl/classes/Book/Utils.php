<?php

namespace ctrl\Book;

use cavWP\Utils as CavWPUtils;

class Utils
{
   public static function clean_content($content)
   {
      $content = str_replace('<br>', '<br/>', $content);
      $content = str_replace('<details', '<div', $content);
      $content = str_replace('</details>', '</div>', $content);
      $content = str_replace('<summary', '<p class="h3"', $content);
      $content = str_replace('</summary>', '</p>', $content);

      return str_replace('&nbsp;', ' ', $content);
   }

   public static function get_author_names($product_ID)
   {
      $authors = \get_field('authors', $product_ID);

      foreach ($authors as $author) {
         $authors_names[] = get_the_author_meta('display_name', $author);
      }

      return CavWPUtils::parse_titles($authors_names);
   }

   public static function get_filename($product_ID, $version = false)
   {
      if (empty($product_ID)) {
         return;
      }

      $product = wc_get_product($product_ID);

      if (empty($product)) {
         return;
      }

      $year    = date('Y');
      $release = get_post_meta($product_ID, 'release', true);

      if (!empty($release)) {
         $year = date('Y', strtotime($release));
      }

      $title  = $product->get_slug();
      $author = '';

      $authors = get_field('authors', $product_ID);

      if (!empty($authors)) {
         foreach ($authors as $author) {
            $authors_names[] = get_the_author_meta('display_name', $author);
         }
         $author = CavWPUtils::parse_titles($authors_names);
      }

      if (false === $version) {
         $version = '*';
      }

      return sanitize_file_name("{$year}-{$author}-{$title}") . '-' . $version;
   }

   public static function get_roles($role = false)
   {
      $roles = [
         '-aft' => esc_html__('Posfácio', 'ctrl'),
         'aqt'  => esc_html__('Autor citado', 'ctrl'),
         'aud'  => esc_html__('Diálogos', 'ctrl'),
         '-aui' => esc_html__('Introdução', 'ctrl'),
         'blw'  => esc_html__('Sinopse', 'ctrl'),
         '-clb' => esc_html__('Colaboração', 'ctrl'),
         'cll'  => esc_html__('Calígrafo', 'ctrl'),
         'clr'  => esc_html__('Colorista', 'ctrl'),
         'cmm'  => esc_html__('Comentários', 'ctrl'),
         'cov'  => esc_html__('Design de capa', 'ctrl'),
         'cph'  => esc_html__('Detenção dos direitos autorais', 'ctrl'),
         'cre'  => esc_html__('Criação', 'ctrl'),
         'crr'  => esc_html__('Correções', 'ctrl'),
         'ctb'  => esc_html__('Contribuição', 'ctrl'),
         'dsr'  => esc_html__('Design', 'ctrl'),
         'edd'  => esc_html__('Direção editorial', 'ctrl'),
         'edt'  => esc_html__('Edição', 'ctrl'),
         'fnd'  => esc_html__('Financiamento', 'ctrl'),
         'fon'  => esc_html__('Fundação', 'ctrl'),
         'ill'  => esc_html__('Ilustrações', 'ctrl'),
         'ivr'  => esc_html__('Entrevistas', 'ctrl'),
         'ltr'  => esc_html__('Letrista', 'ctrl'),
         'lyr'  => esc_html__('Composição de letras', 'ctrl'),
         'orm'  => esc_html__('Organização', 'ctrl'),
         'pfr'  => esc_html__('Revisão', 'ctrl'),
         'res'  => esc_html__('Pesquisa', 'ctrl'),
         'rev'  => esc_html__('Resenha', 'ctrl'),
         'spn'  => esc_html__('Patrocínio', 'ctrl'),
         'stl'  => esc_html__('storyteller', 'ctrl'),
         'trl'  => esc_html__('Tradução', 'ctrl'),
         'wac'  => esc_html__('Comentários adicionais', 'ctrl'),
         'wal'  => esc_html__('Composição adicional de letras', 'ctrl'),
         'wam'  => esc_html__('Materiais complementares', 'ctrl'),
         'wat'  => esc_html__('Textos adicionais', 'ctrl'),
         'waw'  => esc_html__('Conteúdos adicionais', 'ctrl'),
         'wft'  => esc_html__('Interlúdios', 'ctrl'),
         'wfw'  => esc_html__('Prólogo', 'ctrl'),
         'win'  => esc_html__('Introdução', 'ctrl'),
         'wpr'  => esc_html__('Prefácio', 'ctrl'),
         'pbl'  => esc_html__('Publicação', 'ctrl'),
         'pht'  => esc_html__('Fotos', 'ctrl'),
      ];

      if ($role) {
         return $roles[$role] ?? $role;
      }

      asort($roles);

      return $roles;
   }

   public static function invert_name($name)
   {
      $names = explode(' ', trim($name));

      if (count($names) <= 1) {
         return $name;
      }

      $last  = array_pop($names);
      $names = implode(' ', $names);

      return "{$last}, {$names}";
   }
   public static function parse_blocks($blocks, $epub_type = false)
   {
      if (!is_array($blocks)) {
         $blocks = parse_blocks($blocks);
      }

      $content = '';

      foreach ($blocks as $block) {
         switch ($block['blockName']) {
            case 'core/paragraph':
               if ('<p></p>' === trim($block['innerHTML'])) {
                  $block['innerHTML'] = '<p><br /></p>';
               } else {
                  $align = $block['attrs']['align'] ?? 'justify';

                  if ('justify' === $align) {
                     $classes = 'has-text-align-justify';

                     if (false !== $epub_type) {
                        $classes .= ' hyphens-auto';
                     }

                     $block['innerHTML'] = str_replace('<p class="', "<p class=\"{$classes}", $block['innerHTML']);
                     $block['innerHTML'] = str_replace('<p>', "<p class=\"{$classes}\">", $block['innerHTML']);
                  }
               }
               break;

            case 'core/list':
            case 'core/quote':
            case 'core/details':
            case 'core/group':
               $block['innerHTML'] = $block['innerContent'][0];
               $block['innerHTML'] .= self::parse_blocks($block['innerBlocks'], $epub_type);
               $block['innerHTML'] .= $block['innerContent'][count($block['innerContent']) - 1];

               if ('core/quote' === $block['blockName'] && 'epigraph' === $epub_type) {
                  $block['innerHTML'] = str_replace('<blockquote', '<blockquote epub:type="epigraph" role="doc-epigraph" id="epigraph"', $block['innerHTML']);
               }

               break;

            case 'core/list-item':
               if ('bibliography' === $epub_type) {
                  $block['innerHTML'] = str_replace('<li', '<li epub:type="biblioentry" role="doc-biblioentry"', $block['innerHTML']);
               }
               break;

            case 'cav/dt':
               if ('glossary' === $epub_type) {
                  $block['innerHTML'] = str_replace('<dt', '<dt epub:type="glossterm"', $block['innerHTML']);
               }
               break;

            case 'cav/dd':
               if ('glossary' === $epub_type) {
                  $block['innerHTML'] = str_replace('<dd', '<dd epub:type="glossdef"', $block['innerHTML']);
               }
               break;

            case null:
            case 'core/verse':
            case 'core/image':
            case 'core/code':
            case 'core/heading':
            case 'core/separator':
            case 'core/html': // trust
            case 'core/media-text': // later
            case 'core/table':// later
               break;

            case 'core/embed':
               $block['innerHTML'] = '';
               break;

            default:
               debug($block);
               break;
         }
         $content .= self::clean_content($block['innerHTML']);
      }

      return $content;
   }
}
