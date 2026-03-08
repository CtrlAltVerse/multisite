<?php

define('BLOCK_STYLES', [
   'core/paragraph' => [
      [
         'name'  => 'hanging',
         'label' => __('Recuo', 'ctrl'),
      ],
      [
         'name'  => 'no-indent',
         'label' => __('Sem recuo', 'ctrl'),
      ],
   ],
   'core/quote' => [
      [
         'name'  => 'digital',
         'label' => __('Digital', 'ctrl'),
      ],
   ],
   'core/figure' => [
      [
         'name'  => 'portrait',
         'label' => __('Página inteira', 'ctrl'),
      ],
   ],
   'core/separator' => [
      [
         'name'  => 'asterism',
         'label' => __('Asteriscos', 'ctrl'),
      ],
   ],
   'core/list' => [
      [
         'name'       => 'default',
         'label'      => __('Padrão', 'ctrl'),
         'is_default' => true,
      ],
      [
         'name'  => 'square',
         'label' => __('Quadrado', 'ctrl'),
      ],
      [
         'name'  => 'circle',
         'label' => __('Circulo', 'ctrl'),
      ],
      [
         'name'  => 'none',
         'label' => __('Nenhum', 'ctrl'),
      ],
      [
         'name'  => 'horizontal',
         'label' => __('Horizontal', 'ctrl'),
      ],
   ],
   'core/table' => [
      [
         'name'  => 'filecard',
         'label' => __('Ficha', 'ctrl'),
      ],
      [
         'name'  => 'borderless',
         'label' => __('Sem bordas', 'ctrl'),
      ],
   ], 'core/pullquote' => [
      [
         'name'  => 'page-center',
         'label' => __('Centralizado', 'ctrl'),
      ], [
         'name'  => 'page-top',
         'label' => __('Topo', 'ctrl'),
      ], [
         'name'  => 'page-bottom',
         'label' => __('Abaixo', 'ctrl'),
      ],
   ],
]);

define('HECTOR_FOLDER', ABSPATH . 'hector' . DIRECTORY_SEPARATOR);
define('HECTOR_EPUB_STORES', [
   'amazon' => 'Amazon Kindle',
   'kobo'   => 'Kobo',
   'apple'  => 'Apple Books',
   'google' => 'Google Books',
]);
define('HECTOR_PDF_FORMATS', [
   'br' => '16×23cm',
]);
define('LOCALES', [
   'en' => 'en_US',
   'pt' => 'pt_BR',
   'es' => 'es_ES',
]);
