<?php

namespace cavEx\Rewards;

use cavWP\Validate;
use WP_REST_Response;
use WP_REST_Server;

class Register_Endpoint
{
   public function __construct()
   {
      add_action('rest_api_init', [$this, 'create_endpoints']);
   }

   public function create_endpoints()
   {
      $Validade = new Validate();

      register_rest_route('cav/v1', '/rewards', [
         'methods'             => WP_REST_Server::READABLE,
         'callback'            => [$this, 'get_rewards'],
         'permission_callback' => '__return_true',
      ]);

      register_rest_route('cav/v1', '/rewards/(?P<id>\d+)', [
         'methods'             => WP_REST_Server::READABLE,
         'callback'            => [$this, 'get_reward'],
         'permission_callback' => '__return_true',
         'args'                => [
            'id' => [
               'required'          => true,
               'type'              => 'numeric',
               'format'            => 'post:unlock',
               'validate_callback' => [$Validade, 'check'],
            ],
         ],
      ]);

      register_rest_route('cav/v1', '/rewards', [
         'methods'             => WP_REST_Server::EDITABLE,
         'callback'            => [$this, 'redeem_reward'],
         'permission_callback' => '__return_true',
         'args'                => [
            'product_reward_ID' => [
               'required'          => true,
               'type'              => 'numeric',
               'format'            => 'post:unlock',
               'minimum'           => 1,
               'validate_callback' => [$Validade, 'check'],
            ],
         ],
      ]);
   }

   public function get_reward($request)
   {
      $reward_ID = $request['id'];
      $reward    = get_post($reward_ID);
      $objects   = get_field('object', $reward_ID);
      $object    = get_post($objects[0]);

      $actions[] = [
         'action'  => 'setAttr',
         'target'  => '#product_img',
         'content' => 'src',
         'extra'   => get_the_post_thumbnail_url($object->ID, 'thumbnail'),
      ];

      $actions[] = [
         'action'  => 'text',
         'target'  => '#product_title',
         'content' => $reward->post_title,
      ];

      $actions[] = [
         'action'  => 'text',
         'target'  => '#product_details',
         'content' => $reward->post_excerpt,
      ];

      $actions[] = [
         'action'  => 'text',
         'target'  => '#product_summary',
         'content' => $object->post_excerpt,
      ];

      $actions[] = [
         'action'  => 'text',
         'target'  => '#product_value',
         'content' => Utils::parse_prices($reward_ID, 'xp')['value'],
      ];

      $actions[] = [
         'action'  => 'setAttr',
         'target'  => '#product_link',
         'content' => 'href',
         'extra'   => get_permalink($object->ID),
      ];

      $actions[] = [
         'action'  => 'method',
         'target'  => '#rewardDetails',
         'content' => 'showModal',
      ];

      $actions[] = [
         'action' => 'show',
         'target' => '#product_submit',
      ];

      return new WP_REST_Response($actions);
   }

   public function get_rewards($only_actions)
   {
      $rewards = Utils::get_rewards('xp');

      $list = '';

      if (is_user_logged_in()) {
         $xp_available = Utils::get_xp_available();
         $list .= '<div class="mb-4 text-right">XP disponível: ' . $xp_available . '</div>';
      }

      $list .= '<ul class="flex flex-col gap-1.75">';

      foreach ($rewards as $reward) {
         $title = implode(', ', $reward->title);
         $link  = get_permalink($reward->object_ID);

         $summary = '';

         if (!empty($reward->summary)) {
            $summary = '<p>' . $reward->summary . '</p>';
         }

         if ($reward->status) {
            $button = <<<HTML
            <a class="shrink-0 py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold cursor-pointer" href="{$link}">
               Abrir
            </a>
            HTML;
         } else {
            $button = <<<HTML
            <button class="shrink-0 py-2 px-4 bg-neutral-100 text-neutral-800 rounded font-semibold cursor-pointer" type="button" x-on:click.prevent="getReward({$reward->reward_ID})">
               {$reward->price} XP
            </button>
            HTML;
         }

         $list .= <<<HTML
         <li class="flex justify-between items-start">
            <span>
               <a href="{$link}"><strong>{$title}</strong></a>
               {$summary}
            </span>
            {$button}
         </li>
         HTML;
      }

      $list .= '</ul>';

      ob_start();
      include_once plugin_dir_path(CAV_EX_FILE) . 'components/rewards-dialog.php';
      $list .= ob_get_clean();

      $actions[] = [
         'action'  => 'html',
         'target'  => '#rewards',
         'content' => $list,
      ];

      if (true === $only_actions) {
         return $actions;
      }

      return new WP_REST_Response($actions);
   }

   public function redeem_reward($request)
   {
      $reward_ID = $request->get_param('product_reward_ID');
      $status    = 200;

      if (!is_user_logged_in()) {
         $status    = 401;
         $actions[] = [
            'action'  => 'toast',
            'content' => 'É preciso estar logado para resgatar uma recompensa.',
         ];
      }

      if (!Utils::is_unlockable($reward_ID)) {
         $status    = 400;
         $actions[] = [
            'action'  => 'toast',
            'content' => 'Você não tem XP o suficiente.',
         ];
      }

      if (!Utils::redeem_reward($reward_ID)) {
         $status    = 500;
         $actions[] = [
            'action'  => 'toast',
            'content' => 'Ocorreu um erro. Tente novamente mais tarde.',
         ];
      }

      if (200 === $status) {
         $actions[] = [
            'action'  => 'toast',
            'content' => 'Resgate feito com sucesso. Acesso Mais detalhes.',
         ];

         $actions[] = [
            'action' => 'hide',
            'target' => '#product_submit',
         ];

         $actions = array_merge($actions, $this->get_rewards(true));
      }

      return new WP_REST_Response($actions, $status);
   }
}
