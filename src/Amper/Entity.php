<?php
namespace Amper;

use \Amper\Core;

class Entity {
  /**
   * Массив всех сущностей с параметрами, методами и аннотациями
   */
  private static $Entities;
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
   * Получение всех полей для БД
   */
  protected function getFields() : array
  {
    $result = [];
    foreach ($this->EntityInfo['properties'] as $key => $value) {
      $result[] = $this->_searchPropertyAnnotation(
        $value,
        $key,
        'Field',
        'name'
      );
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
   * Поиск информации о свойстве в сущности
   *
   * @param array $propertyAnnotations - Массив всех аннотаций к свойству
   * @param string $propertyName - Имя свойства
   * @param string $annotationName - Имя аннотации
   * @param string $param - Искомый параметр в аннотации
   *
   * Пример:
   * @Field($annotationName) : name($param)=fname, type=varchar, length=255
   */
  private function _searchPropertyAnnotation(array $propertyAnnotations, string $propertyName, string $annotationName, string $param) : string
  {
    $annotationExists = array_values(array_filter($propertyAnnotations, function ($el) use (&$annotationName) {
      return $el['annotation'] == $annotationName;
    }))[0];

    if (!$annotationExists || !$annotationExists['params']) {
      return $propertyName;
    }

    $paramExists = array_values(array_filter($annotationExists['params'], function ($el) use (&$param) {
      return $el[0] == $param;
    }))[0];

    if (!$paramExists) {
      return $propertyName;
    }
    return $paramExists[1];
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
