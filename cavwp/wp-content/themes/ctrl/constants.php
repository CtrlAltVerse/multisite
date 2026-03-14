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
define('HECTOR_EPUB_FORMATS', [
   'amazon' => [
      'label'        => 'Kindle',
      'page_size'    => 'Digital',
      'product_name' => 'Amazon',
   ],
   'kobo' => [
      'label'        => 'Kobo',
      'page_size'    => 'Digital',
      'product_name' => 'Kobo',
   ],
   'apple' => [
      'label'        => 'Apple Books',
      'page_size'    => 'Digital',
      'product_name' => 'Apple',
   ],
   'google' => [
      'label'        => 'Google Books',
      'page_size'    => 'Digital',
      'product_name' => 'Google',
   ],
]);
define('HECTOR_PDF_FORMATS', [
   'a4' => [
      'label'         => 'A4',
      'page_size'     => '21×29.7cm',
      'format'        => [210, 297],
      'margin_top'    => 25,
      'margin_bottom' => 35,
      'margin_left'   => 20, // outer
      'margin_right'  => 20, // inner
   ],
]);
define('HECTOR_HTML_FORMATS', [
   'us' => [
      'label'         => 'KDP',
      'product_name'  => 'Importado',
      'product_type'  => 'external',
      'page_size'     => '6×9in',
      'format'        => [155.5, '228.6'],
      'margin_top'    => 13,
      'margin_bottom' => 24,
      'margin_left'   => 12, // outer
      'margin_right'  => 10, // inner
   ],
   'br' => [
      'label'         => 'Nacional',
      'product_name'  => 'Impresso',
      'product_type'  => 'simple',
      'page_size'     => '16×23cm',
      'format'        => [160, 230],
      'margin_top'    => 14.4,
      'margin_bottom' => 24,
      'margin_left'   => 14.5, // outer
      'margin_right'  => 10, // inner
   ],
]);
define('LOCALES', [
   'en' => 'en_US',
   'pt' => 'pt_BR',
   'es' => 'es_ES',
]);
