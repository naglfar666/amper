<?php
namespace Amper\Utils;

class Parser {
  /**
   * Парсинг аннотаций
   *
   * @param string $data - Аннотации к чему-либо
   */
  public static function parseDocComments(string $data) : array
  {
    // Объявляем итоговый массив
    $result = [];
    // Разбиваем через перенос строки аннотации
    $Splitted_Data = explode("\n", $data);
    for ($i = 0; $i < count($Splitted_Data); $i++) {
      // Очищаем их от лишнего
      $arg = str_replace(' ','',str_replace('*','',$Splitted_Data[$i]));
      // Выбираем только читаемые
      if (substr($arg, 0, 1) == '@') {
        // Разбиваем на аннотации с параметрами
        $arg_array = explode(':', $arg);
        $arg_params = null;
        if (isset($arg_array[1])) {
            $arg_params = explode(',', $arg_array[1]);
            for ($x = 0; $x < count($arg_params); $x++) {
              $arg_params[$x] = explode('=', str_replace("\r",'',str_replace("\n",'',$arg_params[$x])));
            }
        }
        // Каждая аннотация возвращается вместе с параметрами
        $result[] = [
          'annotation' => str_replace('@', '', $arg_array[0]),
          'params' => $arg_params,
        ];
      }
    }

    return $result;
  }
  /**
   * Возвращаем microtime в виде float
   */
  public static function parseMicrotime($time)
  {
    list($usec, $sec) = explode(' ', $time);
    return ((float)$usec + (float)$sec);
  }
}
?>
