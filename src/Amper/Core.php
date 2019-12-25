<?php
namespace Amper;

class Core {

  public static $Router;

  private static $Request;

  private static $Response;

  public static $ModuleWatching;

  public static $Modules;

  public static $DatabaseConfig;

  public static $CacheConfig;

  public static $QueueConfig;

  public static $ModulesConfig;

  public static $ModulesEntities = [];

  public function run() : void
  {
    define('AMPER_DIR', __DIR__);
    $this->loadConfig();
    $this->loadScriptCache();
    // Объявляем ключевые элементы
    self::$Modules = self::$ModulesConfig['modules'];
    $this->loadModulesEntities();
    self::$Router = new Router;
    self::$Request = new Request;
    self::$Response = new Response;
    // Регистрируем маршруты, либо подгружаем из кеша
    $this->loadRoutesCache();

    // Парсим тело запроса
    Request::parseRequestBody(self::$Request);
    Request::parseRequestQuery(self::$Request);
    Request::parseRequestHeaders(self::$Request);
    // Подбираем необходимый путь
    $routeFound = self::$Router->findRoute();

    // Загрузка кеша сущностей
    $this->loadEntitiesCache();

    if (sizeof($routeFound) != 0) {
      self::$Request->setParams($routeFound['params']);
      self::$Request->setMethod($routeFound['method']);

      foreach ($routeFound['middlewares'] as $middleware) {
        $this->callMiddleware($middleware, $routeFound['module']);
      }

      $this->callControllerMethod($routeFound['callback'], $routeFound['module']);
    } else {
      if (function_exists('getallheaders')) {
        self::$Response
        ->setStatus(404)
        ->setMeta(['type'=>'error','text'=>'Request URL is not valid'])
        ->toJson()
        ->execute();
      }
      
    }

  }

  /**
   * Вызываем контроллер
   * @param string $handler
   * @param string $moduleName
   */
  private function callControllerMethod(string $handler, string $moduleName) : void
  {
    $handlerArray = explode('@', $handler);

    $controllerName = '\\'.$moduleName.'\\Controllers\\'.$handlerArray[0];
    $Controller = new $controllerName();
    $Method = $handlerArray[1];
    $Controller->$Method(self::$Request, self::$Response);
    self::$Response->execute();
  }

  /**
   * Вызываем промежуточные обработчики
   * @param string $middlewareName
   * @param string $moduleName
   */
  private function callMiddleware(string $middlewareName, string $moduleName) : void
  {
    $middlewareName = '\\'.$moduleName.'\\Middleware\\'.$middlewareName;
    $middleware = new $middlewareName();
    $middleware->handle(self::$Request, self::$Response);
  }
  /**
   * Подгружаем конфиги приложения
   */
  private function loadConfig() : void
  {
    self::$DatabaseConfig = require_once(GLOBAL_DIR.'/config/database.php');
    self::$CacheConfig = require_once(GLOBAL_DIR.'/config/cache.php');
    self::$QueueConfig = require_once(GLOBAL_DIR.'/config/queue.php');
    self::$ModulesConfig = require_once(GLOBAL_DIR.'/config/modules.php');
  }
  /**
   * Подгрузить кеши
   */
  private function loadScriptCache() : bool
  {
    if (self::$CacheConfig['reset_cache'] === true) {
      if (function_exists('opcache_reset')) {
        opcache_reset();
      }
      return false;
    }
    if (self::$CacheConfig['script_cache'] === true) {
      if (function_exists('opcache_compile_file')) {
        $Scripts = [
          'CrudRepository.php', 'DbConnector.php', 'Entity.php', 'Request.php', 'Response.php', 'Router.php',
          'Utils/Parser.php'
        ];
        foreach ($Scripts as $script) {
          if (!opcache_is_script_cached(AMPER_DIR.'/'.$script)) {
            opcache_compile_file(AMPER_DIR.'/'.$script);
          }
        }
      }
    }

    return true;
  }
  /**
   * Подгрузка закешированных маршрутов, либо создание новых кешей
   */
  private function loadRoutesCache()
  {
    if (self::$CacheConfig['router_cache'] === true) {
      if (!is_dir(AMPER_DIR . '/.cache')) {
        if (!mkdir(AMPER_DIR . '/.cache')) throw new \Exception('Could not create .cache dir');
      }
      $route_cache_file = AMPER_DIR . '/.cache/_route_patterns.ch';
      if (!file_exists($route_cache_file)) {
        $this->_registerRoutes();
        file_put_contents($route_cache_file, serialize(self::$Router->getRoutePatterns()));
      } else {
        self::$Router->setRoutePatterns(unserialize(file_get_contents($route_cache_file)));
      }

    } else {
      $this->_registerRoutes();
    }

  }
  /**
   * Подгрузка закешированных сущностей
   */
  private function loadEntitiesCache()
  {
    $Entity = new Entity;

    if (self::$CacheConfig['entities_cache'] === true) {
      if (!is_dir(AMPER_DIR . '/.cache')) {
        if (!mkdir(AMPER_DIR . '/.cache')) throw new \Exception('Could not create .cache dir');
      }
      $entity_cache_file = AMPER_DIR . '/.cache/_entities.ch';
      if (!file_exists($entity_cache_file)) {
        $Entity->_registerEntities();
        file_put_contents($entity_cache_file, serialize($Entity->getEntities()));
      } else {
        $Entity->setEntities(unserialize(file_get_contents($entity_cache_file)));
      }

    } else {
      $Entity->_registerEntities();
    }


  }
  /**
   * Запуск регистрации всех маршрутов
   * Регистрируются маршруты всех подключенных модулей
   */
  private function _registerRoutes() : void
  {
    for ($i = 0; $i < count(self::$Modules); $i++) {
      self::$ModuleWatching = self::$Modules[$i];
      $routesName = '\\' . self::$Modules[$i] . '\\Routes';
      $Routes = new $routesName();
      $Routes->_register(self::$Router);
    }
  }

  /**
   * Подгрузка сущностей всех подключенных модулей
   */
  private function loadModulesEntities() : void
  {
    for ($i = 0; $i < count(self::$Modules); $i++) {
      if (self::$Modules[$i] == 'App') continue;
      $entityLoaderName = '\\' . self::$Modules[$i] . '\\EntityLoader';
      $entityLoader = new $entityLoaderName();
      $moduleEntities = $entityLoader->_register();
      for ($x = 0; $x < count($moduleEntities); $x++) {
        self::$ModulesEntities[] = '\\' . self::$Modules[$i] . '\\Entities\\' . $moduleEntities[$x];
      }
    }
  }
}
?>
