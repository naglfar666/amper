<?php
namespace Amper\Utils;

class EntityHandler {
  /**
   * Поиск параметра среди одной аннотации к полю сущности
   */
  public static function getParamValue($params, $searchParam)
  {
    return array_values(array_filter($params, function ($el) use ($searchParam) {
      return $el[0] == $searchParam;
    }))[0];
  }
  /**
   * Поиск имени среди аннотаций поля сущности
   */
  public static function getFieldName($key, $value)
  {
    for ($i = 0; $i < count($value); $i++) {
      if (stristr($value[$i]['annotation'], 'Field')) {
        $params = $value[$i]['params'];

        // Находим имя поля среди параметров
        if ($params) {
          $nameExists = self::getParamValue($params, 'name');
        }


        if ($nameExists) {
          return $nameExists[1];
        }

        return $key;

      }
    }

  }

}
?>
