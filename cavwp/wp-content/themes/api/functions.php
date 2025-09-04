<?php

add_filter('rest_url_prefix', 'cav_change_rest_api_base');
function cav_change_rest_api_base()
{
   return 'api';
}

include_once 'CAV_Entity_Rest_API.php';
