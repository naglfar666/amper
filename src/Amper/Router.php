<?php
namespace Amper;

class Router {

  private $routePatterns = [];

  public function get(string $path, string $callback, array $middlewares = []) : Router
  {
    $this->routePatterns[$path] = [
      'method' => 'GET',
      'callback' => $callback,
      'middlewares' => $middlewares,
    ];
    return $this;
  }

  public function post(string $path, string $callback, array $middlewares = []) : Router
  {
    $this->routePatterns[$path] = [
      'method' => 'POST',
      'callback' => $callback,
      'middlewares' => $middlewares,
    ];
    return $this;
  }

  public function options(string $path, string $callback, array $middlewares = []) : Router
  {
    $this->routePatterns[$path] = [
      'method' => 'OPTIONS',
      'callback' => $callback,
      'middlewares' => $middlewares,
    ];
    return $this;
  }

  public function group(string $path, array $routes, array $middlewares = []) : Router
  {
    $path = trim($path, '/');
    foreach ($routes as $route) {
      $method = strtolower($route[0]);
      $this->$method($path.'/'.$route[1], $route[2], $middlewares);
    }
    return $this;
  }

  public function getRoutePatterns() : array
  {
    return $this->routePatterns;
  }

  public function setRoutePatterns(array $patterns) : void
  {
    $this->routePatterns = $patterns;
  }

  public function findRoute() : array
  {
    $Patterns = $this->getRoutePatterns();

    foreach ($Patterns as $pattern => $values) {
      if (strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($values['method'])) {
        $pattern = $this->preparePattern($pattern);
        // Если у нас имеется глобальное обозначение
        if (stristr($pattern['regex'], '*')) {
          if (preg_match_all($pattern['regex'],$this->prepareUrl())) {
            return array_merge($values,['params'=>[]]);
          }
        }
        // Во всех остальных случаях прогоняем по параметрам
        if (preg_match($pattern['regex'],$this->prepareUrl(),$params)) {
          $paramsResult = [];
          for ($i = 0; $i < count($pattern['expectedParams']); $i++) {
            $paramsResult[$pattern['expectedParams'][$i]] = $params[$pattern['expectedParams'][$i]];
          }

          return array_merge($values,['params'=>$paramsResult]);
        }
      }
    }

    return [];
  }

  public function preparePattern(string $pattern) : array
  {
    if (stristr($pattern, '*')) {
      return [
        'regex' => '#'.str_replace('*','(.*)',trim($pattern, '/')).'#',
        'expectedParams' => []
      ];
    } else {
      $patternArray = explode('/', trim($pattern, '/'));
      $expectedParams = [];
      foreach ($patternArray as $key => $value) {
        if (stristr($value, '{')) {

          $expectedParams[] = preg_replace('/[^A-Za-z0-9]/', '', $value);

          $value = str_replace('{', '(?<', $value);
          $patternArray[$key] = str_replace('}', '>\w+)', $value);
        }
      }
      return [
        'regex' => '#^'.implode('/',$patternArray).'$#',
        'expectedParams' =>$expectedParams,
      ];
    }

  }

  public function prepareUrl() : string
  {
    $url = $_SERVER['REQUEST_URI'];

    if (stristr($url, '?')) {
      $startPos = mb_strpos($url, '?');
      $url = mb_substr($url, 0, $startPos);
    }

    return trim($url,'/');
  }
}
?>
