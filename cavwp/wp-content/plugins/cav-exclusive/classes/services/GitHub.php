<?php

namespace cavEx\services;

final class GitHub
{
   private $base_url = 'https://api.github.com';
   private $token    = '';

   public function __construct()
   {
      if (defined('GITHUB_TOKEN')) {
         $this->token = GITHUB_TOKEN;
      }
   }

   public function get_commits($repo)
   {
      $request = wp_remote_get("{$this->base_url}/repos/{$repo}/commits?" . http_build_query([
         'per_page' => 15,
      ]), [
         'cache_duration' => '1 day',
         'headers'        => [
            'Accept'               => 'application/vnd.github+json',
            'Authorization'        => "Bearer {$this->token}",
            'X-GitHub-Api-Version' => '2022-11-28',
         ],
      ]);

      if (is_wp_error($request)) {
         return $request;
      }

      if (200 !== \wp_remote_retrieve_response_code($request)) {
         return [];
      }

      return json_decode(\wp_remote_retrieve_body($request), true);
   }
}
