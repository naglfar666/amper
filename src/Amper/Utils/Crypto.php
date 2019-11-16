<?php
namespace Amper\Utils;

class Crypto {
  /**
   * Генерация рандомного ID
   * @param int $length Длина ID. Во избежание коллизий рекомендуется 13
   * @return string
   */
  public static function uniqid(int $length) : string
  {
    if (function_exists('random_bytes')) {
        $bytes = random_bytes(ceil($length / 2));
    } else if (function_exists('openssl_random_pseudo_bytes')) {
        $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
    } else {
        throw new Exception('Невозможно использовать криптографическую функцию');
    }
    return substr(bin2hex($bytes), 0, $length);
  }

}
?>