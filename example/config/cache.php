<?php
return [
  'reset_cache' => true, // In dev mode should be true to reset opcache
  'script_cache' => false, // Allow to use opcache for engine scripts
  'middleware_cache' => false, // Allow to use opcache for middleware
  'router_cache' => false, // Allows to cache all routes in a file
  'router_cache_method' => 'file',
  'entities_cache' => false, // Allow to cache entities in a file
  'entities_cache_method' => 'file'
];
?>
