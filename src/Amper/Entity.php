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
   * Набор полей с названиями для БД
   */
  private $EntityFields = null;

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
      $Methods = $Data->getMethods(); // Все методы сущности
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
      for ($i = 0; $i < count($Methods); $i++) {
        self::$Entities[$Entity_Name]['methods'][$Methods[$i]->getName()] = Utils\Parser::parseDocComments($Methods[$i]->getDocComment());
      }

      // var_dump($Data->getMethods()[0]->getDocComment());
    }
  }
  /**
   * Моментальная установка всех сущностей
   */
  public function setEntities($data)
  {
    self::$Entities = $data;
  }
  /**
   * Получение всех зарегистрированных сущностей
   */
  public function getEntities()
  {
    return self::$Entities;
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
    if ($this->EntityFields != null) {
      return $this->EntityFields;
    }

    $result = [];
    foreach ($this->EntityInfo['properties'] as $key => $value) {
      $result[] = $this->_searchPropertyAnnotation(
        $value,
        $key,
        'Field',
        'name'
      );
    }

    $this->EntityFields = $result;

    return $result;
  }


  /**
   * Transformes input data to corresponding type of its entity
   * @param $vartype
   * @param $data
   * @return bool|float|int|string
   */
  private function transform_type($vartype, $data)
  {
    switch ($vartype) {
      case 'varstring':
        return (string) $data;
      case 'varinteger':
        return (int) $data;
      case 'varfloat':
        return (float) $data;
      case 'boolean':
        return (bool) $data;
      case 'Boolean':
        return (bool) $data;
      case 'bool':
        return (bool) $data;
    }
    return $data;
  }


  /**
   * loads assoc array to Entity so we can deal with entity easily without setting props manually
   * @param array $dataToLoad
   */
  protected function load(array $dataToLoad) : void
  {
    $fields = $this->getFields();
    $properties = $this->getProperties();

    $info = $this->getEntityInfo();

    for ($i = 0; $i < count($fields); $i++) {
      $method = 'set'.ucfirst($properties[$i]);
      $vartype = preg_replace('/\\r/', '', $info['properties'][$properties[$i]][count($info['properties'][$properties[$i]])-1]['annotation']);
      $this->$method($this->transform_type($vartype, $dataToLoad[$fields[$i]]));
    }

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
