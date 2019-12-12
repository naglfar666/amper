<?php
namespace Amper;

Class Request {

  private $body;
  private $headers;
  private $url;
  private $query;
  private $params;
  private $method;

  public static function parseRequestBody(Request $Request) : void
  {
    $requestBody = [];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $phpinput = file_get_contents("php://input");
  		if ($phpinput != ''){
  			$_POST['phpinput'] = $phpinput;
  		}

  		$Headers = getallheaders();
  		$contentType = null;
  		foreach ($Headers as $key => $value) {
  		  if (mb_stristr(strtolower($key), 'content-type')) {
          $contentType = strtolower($value);
        }
      }
  		if (mb_stristr($contentType, 'application/json')) {
        $requestBody = json_decode($_POST['phpinput'], true);
      } else {
        $requestBody = $_POST;
      }
    }

    $Request->setBody($requestBody);
  }

  public static function parseRequestQuery(Request $Request) : void
  {

    $queryContainer = [];

    $url = $_SERVER['REQUEST_URI'];

    if (mb_stristr($url, '?')) {
      $startPos = mb_strpos($url, '?');
      $urlQuery = mb_substr($url, $startPos + 1, strlen($url) - 1);

      foreach (explode('&', $urlQuery) as $chunk) {
        $param = explode("=", $chunk);

        if ($param) {
          $queryContainer[urldecode($param[0])] = urldecode($param[1]);
        }
      }
    }

    $Request->setQuery($queryContainer);
  }

  public static function parseRequestHeaders(Request $Request) : void
  {
    if (function_exists('getallheaders')) {
      $Request->setHeaders(getallheaders());
    }
  }

  public function getBody() : array
  {
    return $this->body;
  }

  public function setBody(array $data) : void
  {
    $this->body = $data;
  }

  public function getHeaders() : array
  {
    return $this->headers;
  }

  public function setHeaders(array $data) : void
  {
    $this->headers = $data;
  }

  public function getUrl() : string
  {
    return $this->url;
  }

  public function setUrl(string $data) : void
  {
    $this->url = $data;
  }

  public function getQuery() : array
  {
    return $this->query;
  }

  public function setQuery(array $data) : void
  {
    $this->query = $data;
  }

  public function getParams() : array
  {
    return $this->params;
  }

  public function setParams(array $data) : void
  {
    $this->params = $data;
  }

  public function getMethod() : string
  {
    return $this->method;
  }

  public function setMethod(string $data) : void
  {
    $this->method = $data;
  }

}
?>
