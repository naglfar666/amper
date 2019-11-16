<?php
namespace Amper;

use Predis\Client;

class Redis {

  public static function create()
  {
    return new Client(Core::$DatabaseConfig['redis']);
  }
}
?>