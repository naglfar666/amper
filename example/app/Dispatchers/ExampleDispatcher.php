<?php
namespace App\Dispatchers;

class ExampleDispatcher {

  public function handle(array $payload)
  {
    echo json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    return true; // To ensure queue manager that your task is done, return true. In other cases task will return to execution again.
  }
}