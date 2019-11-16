<?php
namespace Amper\Utils;

class QueueHandler {

  public static function buildDispatcherName(string $dispatcherName): string
  {
    return preg_replace('/[^\w]/', '', strtolower($dispatcherName));
  }
  
}

?>