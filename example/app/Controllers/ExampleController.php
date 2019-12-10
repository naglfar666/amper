<?php
namespace App\Controllers;

use \Amper\Request;
use \Amper\Response;

class ExampleController {

  public function index(Request $req, Response $res)
  {
    $res
      ->setStatus(200) // Setting up HTTP status code of the response
      ->setMeta(['type' => 'success']) // Building meta data of response
      ->setData(['Request params' => $req->getParams()]) // Passing payload
      ->toJson(); // Encoding to JSON
  }

  public function show(Request $req, Response $res)
  {
    // Body of the request. In case of GET method body always is empty array
    $Body = $req->getBody();
    // Params from the Router. In case of show method, param from example is "product"
    $Params = $req->getParams();
    // Method of the request. POST, GET or another one
    $Method = $req->getMethod();
    // Query from the request URL string. In case of GET method this one will be filled up
    $Query = $req->getQuery();
    // Headers of the request
    $Headers = $req->getHeaders();

    $res
      ->setStatus(200) // Setting up HTTP status code of the response
      ->setMeta(['type' => 'success']) // Building meta data of response
      ->setData(['some' => 'payload']) // Passing payload
      ->toJson(); // Encoding to JSON
  }

  public function example(Request $req, Response $res)
  {
    $res
      ->setStatus(200) // Setting up HTTP status code of the response
      ->setMeta(['type' => 'success']) // Building meta data of response
      ->setData(['Request method' => $req->getMethod()]) // Passing payload
      ->toJson(); // Encoding to JSON
  }

  public function examplePost(Request $req, Response $res)
  {
    $res
      ->setStatus(200) // Setting up HTTP status code of the response
      ->setMeta(['type' => 'success']) // Building meta data of response
      ->setData([
        'Request method' => $req->getMethod(),
        'Request payload' => $req->getBody()
      ]) // Passing payload
      ->toJson(); // Encoding to JSON
  }
}