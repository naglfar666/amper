<?php
namespace App;

use Amper\Router;

class Routes
{

  public function _register(Router $Router) : void
  {
    // Passing array of middlewares as a 3-rd argument
    $Router->options('/api/*', '', [ 'CorsMiddleware' ]);
    // Grouping routes with same middleware
    $Router->group('/api/v1',
      [
        ['GET','{product}', 'ExampleController@index'],
        ['POST','{product}/show', 'ExampleController@show']
      ], [ 'CorsMiddleware' ]);
    // Passing single routes
    $Router
      ->get('/example', 'ExampleController@example', [ 'CorsMiddleware' ])
      ->post('/example', 'ExampleController@examplePost', [ 'CorsMiddleware' ]);
  }
}