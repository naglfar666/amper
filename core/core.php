<?php
namespace core;

use Router;
use Request;
use Response;
use \api\Routes;

class Core {

  public static $Router;

  private static $Request;

  private static $Response;

  public function run() : void
  {
    self::$Router = new Router;
    self::$Request = new Request;
    self::$Response = new Response;
    new Routes;

    Request::parseRequestBody(self::$Request);
    Request::parseRequestQuery(self::$Request);
    Request::parseRequestHeaders(self::$Request);

    $routeFound = self::$Router->findRoute();

    if (sizeof($routeFound) != 0) {
      self::$Request->setParams($routeFound['params']);
      self::$Request->setMethod($routeFound['method']);

      $this->callControllerMethod($routeFound['callback']);
    } else {
      self::$Response
        ->setStatus(404)
        ->setMeta(['type'=>'error','text'=>'Request URL is not valid'])
        ->toJson()
        ->execute();
    }

  }

  private function callControllerMethod(string $controller) : void
  {
    call_user_func($controller, self::$Request, self::$Response);
    self::$Response->execute();
  }
}
?>
