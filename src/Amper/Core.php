<?php
namespace Amper;
class Core {

  public static $Router;

  private static $Request;

  private static $Response;

  public function run() : void
  {
    // Объявляем ключевые элементы
    self::$Router = new Router;
    self::$Request = new Request;
    self::$Response = new Response;
    // Регистрируем маршруты
    $Routes = new \App\Routes();
    $Routes->_register(self::$Router);
    // Парсим тело запроса
    Request::parseRequestBody(self::$Request);
    Request::parseRequestQuery(self::$Request);
    Request::parseRequestHeaders(self::$Request);
    // Подбираем необходимый путь
    $routeFound = self::$Router->findRoute();

    if (sizeof($routeFound) != 0) {
      self::$Request->setParams($routeFound['params']);
      self::$Request->setMethod($routeFound['method']);

      foreach ($routeFound['middlewares'] as $middleware) {
        $this->callMiddleware($middleware);
      }

      $this->callControllerMethod($routeFound['callback']);
    } else {
      self::$Response
        ->setStatus(404)
        ->setMeta(['type'=>'error','text'=>'Request URL is not valid'])
        ->toJson()
        ->execute();
    }

  }
  /**
   * Вызываем контроллер
   */
  private function callControllerMethod(string $handler) : void
  {
    $handlerArray = explode('@', $handler);

    $controllerName = '\\App\\Controllers\\'.$handlerArray[0];
    $Controller = new $controllerName();
    $Method = $handlerArray[1];
    $Controller->$Method(self::$Request, self::$Response);
    self::$Response->execute();
  }

  private function callMiddleware(string $middlewareName) : void
  {
    $middlewareName = '\\App\\Middleware\\'.$middlewareName;
    $middleware = new $middlewareName();
    $middleware->handle(self::$Request, self::$Response);
  }
}
?>
