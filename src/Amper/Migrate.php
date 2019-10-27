<?php
namespace Amper;

use \Amper\Utils\EntityHandler;

class Migrate extends Entity{
  /**
   * Полностью обновление всех таблиц
   */
  public function refresh()
  {
    $Entities = $this->getEntities();
    foreach ($Entities as $key => $value) {
      echo 'Refreshing entity: ' . $key . PHP_EOL;
      $table = $value['info'][0]['params'][0][1];
      $this->_drop($table);
      $fields = $this->_prepareFields($value['properties']);
      $this->_create($table, $fields);
    }
  }
  /**
   * Удаляем таблицу
   */
  private function _drop($table)
  {
    echo 'Dropping table: ' . Core::$DatabaseConfig['connection']['prefix'] . $table . PHP_EOL;
    $db = new DbConnector;
    $db->query('DROP TABLE IF EXISTS ' . Core::$DatabaseConfig['connection']['prefix'] . $table);
  }
  /**
   * Создаем таблицу
   */
  private function _create($table, $fields)
  {
    echo 'Creating table: ' . Core::$DatabaseConfig['connection']['prefix'] . $table . PHP_EOL;
    $db = new DbConnector;
    $db->query('CREATE TABLE IF NOT EXISTS ' . Core::$DatabaseConfig['connection']['prefix'] . $table . ' ' . $fields);
  }
  /**
   * Подготавливаем поля
   */
  private function _prepareFields($properties)
  {
    $fields = '(';
    $primaryKeyString = '';
    foreach ($properties as $key => $value) {

      $fieldStart = '';
      $fieldEnd = '';

      for ($i = 0; $i < count($value); $i++) {

        if (stristr($value[$i]['annotation'], 'Field')) {
          $params = $value[$i]['params'];
          // Получаем имя поля
          $fieldStart .= ' ' . EntityHandler::getFieldName($key, $value) . ' ';
          // Находим тип поля среди параметров
          if ($params) {
            $typeExists = EntityHandler::getParamValue($params, 'type');
          }

          if (!$typeExists) {
            $fieldType = 'varchar';
          } else {
            $fieldType = $typeExists[1];
          }

          $fieldStart .= ' ' . mb_strtoupper($fieldType) . ' ';
          // Находим длину поля
          if ($params) {
            $fieldLengthExists = EntityHandler::getParamValue($params, 'length');
          }


          if ($fieldLengthExists) {
            $fieldStart .= ' (' . $fieldLengthExists[1] . ') ';
          } else {
            switch (mb_strtolower($fieldType)) {
              case 'varchar':
                $fieldStart .= ' (255) ';
                break;
              case 'int':
                $fieldStart .= ' (11) ';
                break;
              case 'text':
                $fieldStart .= ' (1024) ';
                break;
            }
          }
        }

        if (stristr($value[$i]['annotation'], 'Id')) {
          $fieldEnd .= ' UNSIGNED ';
          // $fieldEnd .= ' UNSIGNED PRIMARY KEY ';
          $primaryKeyString = ' PRIMARY KEY (' . EntityHandler::getFieldName($key, $value) . ')';
        }

        if (stristr($value[$i]['annotation'],'GeneratedValue')) {
          $params = $value[$i]['params'];

          if ($params) {
            $fieldStrategyExists = EntityHandler::getParamValue($params, 'strategy');
            $fieldEnd .= ' ' . $fieldStrategyExists[1] . ' ';
          }
        }

        if (stristr($value[$i]['annotation'],'NotNull')) {
          $fieldEnd .= ' NOT NULL ';
        }

        if (stristr($value[$i]['annotation'],'Nullable')) {
          $fieldEnd .= ' NULL DEFAULT NULL ';
        }
      }

      $fields .= $fieldStart . $fieldEnd . ' , ';
    }

    $fields .= $primaryKeyString;
    $fields .= ' ) ENGINE = InnoDB;';

    return $fields;
  }
}
?>
