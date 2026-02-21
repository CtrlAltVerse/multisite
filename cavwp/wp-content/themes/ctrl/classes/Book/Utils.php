<?php

namespace ctrl\Book;

use cavWP\Utils as CavWPUtils;

class Utils
{
   public static function get_filename($product_ID, $version = false)
   {
      if (empty($product_ID)) {
         return;
      }

      $product = wc_get_product($product_ID);

      if (empty($product)) {
         return;
      }

      $year         = date('Y');
      $date_created = $product->get_date_created();

      if (!empty($date_created)) {
         $year = $date_created->date('Y');
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

      if (!$version) {
         $version = '*';
      }

      return sanitize_file_name("{$year}-{$author}-{$title}") . '-' . $version . '.epub';
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
      ];

      if ($role) {
         return $roles[$role] ?? $role;
      }

      return $roles;
   }
}
