<?php
namespace Amper;

use \Amper\Core;

class Entity {
  /**
   * Массив всех сущностей с параметрами, методами и аннотациями
   */
  protected static $Entities;
  /**
   * Текущая дочерняя сущность, с которой мы работаем
   */
  private $EntityInfo;

  /**
   * Регистрируем все подключенные сущности
   */
  public function _registerEntities() : void
  {
    self::$Entities = [];
    // Получаем все подключенные сущности
    $Entities = Core::$DatabaseConfig['entities'];
    foreach ($Entities as $entity) {
      // Получаем класс каждой сущности
      $Data = new \ReflectionClass('\\App\\Entities\\'.$entity);
      $Properties = $Data->getProperties(); // Все свойства сущности
      $Entity_Name = $Data->getName(); // Имя сущности с пространствами имен
      self::$Entities[$Entity_Name] = [
        'info' => Utils\Parser::parseDocComments($Data->getDocComment()),
        'properties' => [],
        'methods' => []
      ];
      // Разбираем все аннотации к свойствам сущности
      for ($i = 0; $i < count($Properties); $i++) {
        self::$Entities[$Entity_Name]['properties'][$Properties[$i]->getName()] = Utils\Parser::parseDocComments($Properties[$i]->getDocComment());
      }

      // var_dump($Data->getMethods()[0]->getDocComment());
    }
  }
  /**
   * Получение всех свойств сущности
   */
  protected function getProperties() : array
  {
    $result = [];
    foreach ($this->EntityInfo['properties'] as $key => $value) {
      $result[] = $key;
    }
    return $result;
  }
  /**
   * Получение таблицы сущности
   */
  protected function getTable() : string
  {
    for ($i = 0; $i < count($this->EntityInfo['info']); $i++) {
      if ($this->EntityInfo['info'][$i]['annotation'] == 'Table') {
        for ($x = 0; $x < count($this->EntityInfo['info'][$i]['params']); $x++) {
          if ($this->EntityInfo['info'][$i]['params'][$x][0] == 'name') {
            return $this->EntityInfo['info'][$i]['params'][$x][1];
          }
        }
      }
    }
  }
  /**
   * Получение всей информации о сущности
   */
  protected function getEntityInfo() : array
  {
    return $this->EntityInfo;
  }
  /**
   * При любой попытке вызова методов класса обновляем информацию о текущей сущности
   */
  public function __call($method, $params)
  {
    $this->EntityInfo = self::$Entities[get_called_class()];
    if(method_exists($this, $method))
		{
		  return call_user_func_array(array($this, $method), $params);
		}
  }

}
?>
