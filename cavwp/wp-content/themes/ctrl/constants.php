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
      'currencies'   => [
         'USD' => '[name="data[digital][channels][amazon][US][price_vat_inclusive]"]',
         'INR' => '[name="data[digital][channels][amazon][IN][price_vat_inclusive]"]',
         'GBP' => '[name="data[digital][channels][amazon][UK][price_vat_inclusive]"]',
         'EUR' => '[name="data[digital][channels][amazon][DE][price_vat_inclusive]"],[name="data[digital][channels][amazon][FR][price_vat_inclusive]"],[name="data[digital][channels][amazon][ES][price_vat_inclusive]"],[name="data[digital][channels][amazon][IT][price_vat_inclusive]"],[name="data[digital][channels][amazon][NL][price_vat_inclusive]"]',
         'JPY' => '[name="data[digital][channels][amazon][JP][price_vat_inclusive]"]',
         'BRL' => '[name="data[digital][channels][amazon][BR][price_vat_inclusive]"]',
         'CAD' => '[name="data[digital][channels][amazon][CA][price_vat_inclusive]"]',
         'MXN' => '[name="data[digital][channels][amazon][MX][price_vat_inclusive]"]',
         'AUD' => '[name="data[digital][channels][amazon][AU][price_vat_inclusive]"]',
      ],
   ],
   'kobo' => [
      'label'        => 'Kobo',
      'page_size'    => 'Digital',
      'product_name' => 'Kobo',
      'currencies'   => [
         'USD' => '[aria-label="Price for currency code USD"]',
         'HKD' => '[aria-label="Price for currency code HKD"]',
         'CHF' => '[aria-label="Price for currency code CHF"]',
         'TWD' => '[aria-label="Price for currency code TWD"]',
         'MXN' => '[aria-label="Price for currency code MXN"]',
         'EUR' => '[aria-label="Price for currency code EUR"]',
         'CAD' => '[aria-label="Price for currency code CAD"]',
         'ZAR' => '[aria-label="Price for currency code ZAR"]',
         'INR' => '[aria-label="Price for currency code INR"]',
         'AUD' => '[aria-label="Price for currency code AUD"]',
         'JPY' => '[aria-label="Price for currency code JPY"]',
         'GBP' => '[aria-label="Price for currency code GBP"]',
         'NZD' => '[aria-label="Price for currency code NZD"]',
         'PHP' => '[aria-label="Price for currency code PHP"]',
         'BRL' => '[aria-label="Price for currency code BRL"]',
      ],
   ],
   'apple' => [
      'label'        => 'Apple Books',
      'page_size'    => 'Digital',
      'product_name' => 'Apple',
      'currencies'   => ['AUD' => '', 'BRL' => '', 'CAD' => '', 'CHF' => '', 'CLP' => '', 'COP' => '', 'CZK' => '', 'DKK' => '', 'EUR' => '', 'GBP' => '', 'HUF' => '', 'JPY' => '', 'MXN' => '', 'NOK' => '', 'NZD' => '', 'PEN' => '', 'PLN' => '', 'RON' => '', 'SEK' => '', 'USD' => ''],
   ],
   'google' => [
      'label'        => 'Google Books',
      'page_size'    => 'Digital',
      'product_name' => 'Google',
      'currencies'   => ['AED' => '', 'AUD' => '', 'BOB' => '', 'BRL' => '', 'CAD' => '', 'CHF' => '', 'CLP' => '', 'COP' => '', 'CRC' => '', 'CZK' => '', 'DKK' => '', 'EGP' => '', 'EUR' => '', 'GBP' => '', 'HKD' => '', 'HUF' => '', 'IDR' => '', 'INR' => '', 'JOD' => '', 'JPY' => '', 'KRW' => '', 'KZT' => '', 'MXN' => '', 'MYR' => '', 'NOK' => '', 'NZD' => '', 'PEN' => '', 'PHP' => '', 'PLN' => '', 'PYG' => '', 'QAR' => '', 'RON' => '', 'RUB' => '', 'SAR' => '', 'SEK' => '', 'SGD' => '', 'THB' => '', 'TRY' => '', 'TWD' => '', 'UAH' => '', 'USD' => '', 'VND' => '', 'ZAR' => ''],
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
