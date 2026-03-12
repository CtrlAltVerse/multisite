<?php

namespace ctrl\Hector;

use WP_Theme_JSON_Resolver;

class Theme_JSON_Converter
{
   private $_vars;
   private $settings;
   private $styles;
   private $target;

   public function __construct($target = 'all')
   {
      $this->target   = $target;
      $this->settings = WP_Theme_JSON_Resolver::get_merged_data()->get_settings();
      $theme_json     = WP_Theme_JSON_Resolver::get_merged_data()->get_data();
      $this->styles   = $theme_json['styles'];

      $this->_vars['--wp--style--block-gap'] = $this->styles['spacing']['blockGap'];
      $this->populate_custom_vars();
   }

   public function get_css()
   {
      $css = '';
      $css .= $this->_style_for_block('body', $this->styles['typography']);
      $css .= $this->parse_font_families();
      $css .= $this->parse_font_sizes();
      $css .= $this->parse_colors();
      $css .= $this->parse_elements();
      $css .= $this->parse_blocks();
      $css .= $this->styles['css'];

      return $css;
   }

   private function _style_for_block($block, $data, $variation = '', $element = '')
   {
      $element = match ($element) {
         'heading' => '.wp-block-heading',
         default   => $element ,
      };

      $element = match ($block) {
         'core/pullquote' => 'blockquote ',
         default          => '',
      } . $element;

      $block = str_replace('core/', '.wp-block-', $block);

      $block = match ($block) {
         'link' => 'a',

         default => $block,
      };

      if (!empty($variation)) {
         $variation = ".is-style-{$variation}";
      }

      $variations = $data['variations'] ?? [];
      $elements   = $data['elements']   ?? [];
      $raw        = $data['css']        ?? '';

      unset($data['variations'],$data['elements'], $data['css']);

      $middle = '';

      foreach ($data as $key1 => $value1) {
         if (is_null($value1)) {
            continue;
         }

         $prop1 = $this->_verify_prop($key1, 1);

         if (false !== $prop1) {
            if (!is_array($value1)) {
               $value = $this->_verify_val($value1);
               $middle .= "{$prop1}: {$value};";
               continue;
            }

            foreach ($value1 as $key2 => $value2) {
               if (is_null($value2)) {
                  continue;
               }

               $prop2 = $this->_verify_prop($key2, 2);

               if (!empty($prop1) && !str_ends_with($prop1, '-')) {
                  $prop1 .= '-';
               }

               if (false !== $prop2) {
                  if (!is_array($value2)) {
                     $value = $this->_verify_val($value2);
                     $middle .= "{$prop1}{$prop2}: {$value};";
                     continue;
                  }

                  foreach ($value2 as $key3 => $value3) {
                     if (is_null($value3)) {
                        continue;
                     }

                     $prop3 = $this->_verify_prop($key3, 3);

                     if (!empty($prop2) && !str_ends_with($prop2, '-')) {
                        $prop2 .= '-';
                     }

                     if (false !== $prop3) {
                        if (!is_array($value3)) {
                           $value = $this->_verify_val($value3);
                           $middle .= "{$prop1}{$prop2}{$prop3}: {$value};";
                           continue;
                        }
                     }
                  }
               }
            }
         }
      }

      $selector = trim($block . $variation . ' ' . $element);
      $close    = '}';
      $css      = '';

      if (!empty($middle)) {
         $css .= $selector . '{' . $middle . $close;
      }

      if (!empty($variations)) {
         foreach ($variations as $key => $variation) {
            $css .= $this->_style_for_block($block, $variation, $key);
         }
      }

      if (!empty($elements)) {
         foreach ($elements as $key => $element) {
            $css .= $this->_style_for_block($block, $element, element: $key);
         }
      }

      if (!empty($raw)) {
         $css .= $this->_style_from_raw($raw, $selector);
      }

      return $css;
   }

   private function _style_from_raw($css, $selector)
   {
      $processed_css = '';

      $parts = explode('&', $css);

      foreach ($parts as $part) {
         if (empty($part)) {
            continue;
         }
         $is_root_css = (!str_contains($part, '{'));

         if ($is_root_css) {
            $processed_css .= trim($selector) . '{' . $this->_verify_val($part) . '}';
         } else {
            $part = explode('{', str_replace('}', '', $part));

            if (count($part) !== 2) {
               continue;
            }
            $nested_selector = $part[0];
            $css_value       = $part[1];

            $matches            = [];
            $has_pseudo_element = preg_match('/([>+~\s]*::[a-zA-Z-]+)/', $nested_selector, $matches);
            $pseudo_part        = $has_pseudo_element ? $matches[1] : '';
            $nested_selector    = $has_pseudo_element ? str_replace($pseudo_part, '', $nested_selector) : $nested_selector;

            if (str_starts_with($nested_selector, ' ')) {
               if (!$selector || !$nested_selector) {
                  $part_selector = $nested_selector;
               } else {
                  $scopes    = explode(',', $selector);
                  $selectors = explode(',', $nested_selector);

                  $selectors_scoped = [];

                  foreach ($scopes as $outer) {
                     foreach ($selectors as $inner) {
                        $outer = trim($outer);
                        $inner = trim($inner);

                        if (!empty($outer) && !empty($inner)) {
                           $selectors_scoped[] = $outer . ' ' . $inner;
                        } elseif (empty($outer)) {
                           $selectors_scoped[] = $inner;
                        } elseif (empty($inner)) {
                           $selectors_scoped[] = $outer;
                        }
                     }
                  }

                  $part_selector = implode(', ', $selectors_scoped);
               }
            } else {
               if (!str_contains($selector, ',')) {
                  $part_selector = $selector . $nested_selector;
               } else {
                  $new_selectors = [];
                  $selectors     = explode(',', $selector);

                  foreach ($selectors as $sel) {
                     $new_selectors[] = $sel . $nested_selector;
                  }
                  $part_selector = implode(',', $new_selectors);
               }
            }

            $final_selector = "{$part_selector}{$pseudo_part}";

            $processed_css .= $final_selector . '{' . $this->_verify_val($css_value) . '}';
         }
      }

      return $processed_css;
   }

   private function _verify_prop($prop, $level = 1)
   {
      switch ($level) {
         case 3:
            break;

         case 2:
            $prop = match ($prop) {
               'fontFamily' => 'font-family',
               'text'       => 'color',
               'gradient'   => 'background',
               default      => $prop,
            };
            break;

         case 1:
            $prop = match ($prop) {
               'color'      => '',
               'spacing'    => '',
               'typography' => '',
               'background' => '',
               'dimensions' => '',
               default      => $prop,
            };
            break;

         default:
            $prop = false;
            break;
      }

      if (is_bool($prop)) {
         return $prop;
      }

      return strtolower(preg_replace('/(?<!^|\ )[A-Z]/', '-$0', $prop));
   }

   private function _verify_val($value)
   {
      if (is_null($value)) {
         return '';
      }

      $value = stripslashes(rtrim(trim($value), ';'));

      if (!str_contains($value, 'var(')) {
         return $value;
      }

      foreach ($this->_vars as $search => $replace) {
         $value = str_replace("var({$search})", $replace, $value);
      }

      if (!str_contains($value, 'calc(')) {
         return $value;
      }

      preg_match('/calc\((.*)\)/', $value, $matches);

      if (empty($matches)) {
         return $value;
      }

      $units = ['rem', 'em', 'px', '%', 'cm', 'mm', 'ch', 'vw', 'vh', 'in', 'pt', 'pc', 'ex', 'vmin', 'vmax'];

      $items    = explode(' ', $matches[1]);
      $operator = '';
      $unit     = '';

      $items = array_map(function($item) use ($units, &$operator, &$unit) {
         if (in_array($item, ['*', '+', '-', '/'])) {
            $operator = $item;

            return $item;
         }

         foreach ($units as $t_unit) {
            if (str_ends_with($item, $t_unit)) {
               $unit = $t_unit;
               $item = str_replace($unit, '', $item);
               break;
            }
         }

         return (float) $item;
      }, $items);

      switch ($operator) {
         case '*':
            $result = $items[0] * $items[2] . $unit;
            break;

         case '-':
            $result = $items[0] - $items[2] . $unit;
            break;

         case '+':
            $result = $items[0] + $items[2] . $unit;
            break;

         case '/':
            $result = $items[0] / $items[2] . $unit;
            break;

         default:
            break;
      }

      return str_replace($matches[0], $result, $value);
   }

   private function parse_blocks()
   {
      $css = '';

      foreach ($this->styles['blocks'] as $block => $data) {
         $css .= $this->_style_for_block($block, $data);
      }

      return $css;
   }

   private function parse_colors()
   {
      $css = '';

      $colors = [];

      if ($this->settings['color']['defaultPalette']) {
         $colors = array_merge($this->settings['color']['palette']['default'], $colors);
      }

      $colors = array_merge($this->settings['color']['palette']['theme'] ?? [], $colors);

      if (!empty($colors)) {
         foreach ($colors as $color) {
            $css .= <<<CSS
            .has-{$color['slug']}-color {
               color: {$color['color']};
            }
            .has-{$color['slug']}-background-color {
               background-color: {$color['color']};
            }
            .has-{$color['slug']}-border-color {
               border-color: {$color['color']};
            }
            CSS;

            $this->_vars['--wp--preset--color--' . $color['slug']] = $color['color'];
         }
      }

      $duotone = [];

      if ($this->settings['color']['defaultDuotone']) {
         $duotone = array_merge($this->settings['color']['duotone']['default'], $duotone);
      }

      $gradients = [];

      if ($this->settings['color']['defaultGradients']) {
         $gradients = array_merge($this->settings['color']['gradients']['default'], $gradients);
      }

      return $css;
   }

   private function parse_elements()
   {
      $css = '';

      foreach ($this->styles['elements'] as $element => $data) {
         $css .= $this->_style_for_block($element, $data);
      }

      return $css;
   }

   private function parse_font_families()
   {
      $families = $this->settings['typography']['fontFamilies']['theme'];

      $css = '';

      foreach ($families as $family) {
         if ('pdf' === $this->target) {
            $font_face = $family['slug'];
         } else {
            $font_face = $family['fontFamily'];
         }

         $css .= <<<CSS
         .has-{$family['slug']}-font-family {
            font-family: {$font_face};
         }
         CSS;

         $this->_vars['--wp--preset--font-family--' . $family['slug']] = $family['fontFamily'];
      }

      return $css;
   }

   private function parse_font_sizes()
   {
      $sizes = [];

      if ($this->settings['typography']['defaultFontSizes']) {
         $sizes = array_merge($this->settings['typography']['fontSizes']['default'], $sizes);
      }

      $sizes = array_merge($this->settings['typography']['fontSizes']['theme'] ?? [], $sizes);

      $css = '';

      foreach ($sizes as $size) {
         $css .= <<<CSS
         .has-{$size['slug']}-font-size {
            font-size: {$size['size']};
         }

         @media amzn-mobi {
            .has-{$size['slug']}-font-size {
               font-size: {$size['slug']};
            }
         }
         CSS;

         $this->_vars['--wp--preset--font-size--' . $size['slug']] = $size['size'];
      }

      return $css;
   }

   private function parse_ratios()
   {
      $ratios = [];

      if ($this->settings['dimensions']['defaultAspectRatios']) {
         $ratios = array_merge($this->settings['dimensions']['aspectRatios']['default'], $ratios);
      }

      $ratios = array_merge($this->settings['dimensions']['aspectRatios']['theme'] ?? [], $ratios);

      return '';
   }

   private function parse_shadows()
   {
      $shadows = [];

      if ($this->settings['shadows']['defaultPresets']) {
         $shadows = array_merge($this->settings['shadow']['presets']['default'], $shadows ?? []);
      }

      $shadows = array_merge($this->settings['dimensions']['aspectRatios']['theme'] ?? [], $shadows);
   }

   private function populate_custom_vars()
   {
      if (empty($this->settings['custom'])) {
         return;
      }

      foreach ($this->settings['custom'] as $major_key => $values) {
         if (!is_array($values)) {
            $this->_vars["--wp--custom--{$major_key}"] = $values;
            continue;
         }

         foreach ($values as $minor_key => $value) {
            if ('images' === $major_key && 'asterism' === $minor_key && 'epub' === $this->target) {
               $value = 'images/asterism.png';
            }

            $this->_vars["--wp--custom--{$major_key}--{$minor_key}"] = $value;
         }
      }
   }
}
