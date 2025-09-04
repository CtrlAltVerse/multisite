<?php

class CAV_Entity_Rest_API extends \WP_REST_Posts_Controller
{
   protected $fields;

   public function __construct($post_type)
   {
      parent::__construct($post_type);

      $this->fields = $this->convert_acf_fields_to_rest_params();
   }

   public function get_collection_params()
   {
      return $this->fields;
   }

   public function get_items($request)
   {
      $params = $request->get_params();

      $query_args = [
         'post_type'    => $this->post_type,
         'post_status'  => 'publish',
         'has_password' => false,
         'orderby'      => 'rand',
      ];

      foreach ($params as $key => $value) {
         $current_field = $this->fields[$key];

         $opts = [
            'key'   => $key,
            'value' => $value,
         ];

         switch ($current_field['type']) {
            case 'boolean':
               $opts['compare'] = '==';
               $opts['value']   = (int) $value;
               break;

            default:
               $opts['compare'] = 'LIKE';
               break;
         }

         $query_args['meta_query'][] = $opts;
      }

      $posts_query  = new \WP_Query();
      $query_result = $posts_query->query($query_args);

      foreach ($query_result as $post) {
         if (!$this->check_read_permission($post)) {
            continue;
         }

         $posts[] = $this->prepare_response_for_collection($post);
      }

      return rest_ensure_response($posts);
   }

   public function prepare_response_for_collection($post)
   {
      $data['name']    = $post->post_title;
      $data['summary'] = $post->post_excerpt;

      $fields_keys = array_keys($this->fields);

      foreach ($fields_keys as $key) {
         $data[$key] = get_field($key, $post->ID);
      }

      return $data;
   }

   protected function convert_acf_fields_to_rest_params()
   {
      $params       = [];
      $field_groups = acf_get_field_groups(['post_type' => $this->post_type]);

      foreach ($field_groups as $field_group) {
         $fields = acf_get_fields($field_group['key']);

         foreach ($fields as $field) {
            $opts['title']       = $field['label'];
            $opts['description'] = $field['instructions'];

            switch ($field['type']) {
               case 'select':
               case 'radio':
                  $opts['type'] = 'string';
                  $opts['enum'] = array_keys($field['choices']);
                  break;

               case 'true_false':
                  $opts['type'] = 'boolean';
                  break;

               default:
                  $opts['type'] = 'string';
                  break;
            }

            $params[$field['name']] = $opts;
            unset($opts);
         }
      }

      return $params;
   }
}
