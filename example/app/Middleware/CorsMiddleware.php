<?php
namespace App\Middleware;

use Amper\Request;
use Amper\Response;

class CorsMiddleware {
  // By default Amper will search for handle() method and passes into this method Request and Response instances
  public function handle(Request $request, Response $response)
  {
    header('Content-Type: text/html; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    // If request method is OPTIONS, send response directly to client, in other cases set proper headers
    if ($request->getMethod() == 'OPTIONS') {
      $response
        ->setMeta(['type' => 'success'])
        ->toJson()
        ->execute();
      $response->finish();
    }
  }

}