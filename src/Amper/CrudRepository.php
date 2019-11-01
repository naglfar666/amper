<?php
namespace Amper;

use \Amper\Utils\EntityHandler;

class CrudRepository {

  private $Entity;

  public function __construct(string $entity)
  {
    $this->Entity = new $entity;
  }
  /**
   * Шаблон-маска для самых частых запросов
   * @param string $where - Условие на выборку
   * @param array $prepare - Для автозамены массив
   * @param string $order - По какому полю сортировать
   * @param string $desc - Признак сортировки
   */
  protected function simpleQuery($where, $prepare = [], $order = null, $desc = null) : array
  {
    $query = 'SELECT ' . implode(', ', $this->Entity->getFields())
          . ' FROM ' . Core::$DatabaseConfig['connection']['prefix'] . $this->Entity->getTable()
          . ' WHERE '.$where;

    if ($order) {
      $query .= ' ORDER BY ' . $order;
    }

    if ($desc) {
      $query .= ' ' . $desc;
    }

    $db = new DbConnector;
    return $db->query($query, $prepare);
  }
  /**
   * Пустой шаблон-маска для прямых SQL запросов
   *
   * @param string $query - Прямой SQL запрос
   * @param array $prepare - Подготовка данных
   */
  protected function query(string $query, array $prepare = []) : array
  {
    $db = new DbConnector;
    return $db->query($query, $prepare);
  }
  /**
   * Добавление новой записи с возвращением ID
   */
  protected function insert(string $query, array $prepare = []) : string
  {
    $db = new DbConnector;
    return $db->insert($query, $prepare);
  }
  /**
   * Выборка всех записей из таблицы
   * Смотреть параметры метода simpleQuery
   */
  protected function findAll($where = 'id > 0', $prepare = [], $order = 'id', $desc = 'ASC') : array
  {
    return $this->simpleQuery($where, $prepare, $order, $desc);
  }
  /**
   * Выборка всех записей из БД в обратном порядке
   * Смотреть параметры метода simpleQuery
   */
  protected function findAllDesc($where = 'id > 0', $prepare = [], $order = 'id', $desc = 'DESC') : array
  {
    return $this->simpleQuery($where, $prepare, $order, $desc);
  }
  /**
   * Сохранение экземпляра сущности
   * Если присутствует id, то обновляем. Если нет, то создаем новую
   */
  protected function save($Entity)
  {
    $query = '';

    if ($Entity->getId() == null) {
      $methodName = 'insert';
      $Query_Array = $this->generateInsertFields($Entity);
      $query .= 'INSERT INTO ' . Core::$DatabaseConfig['connection']['prefix'].$this->Entity->getTable() . ' '
      . $Query_Array['query'];

    } else {
      $methodName = 'query';
      $Query_Array = $this->generateUpdateFields($Entity);
      $query .= 'UPDATE ' . Core::$DatabaseConfig['connection']['prefix'].$this->Entity->getTable() . ' SET '
      . $Query_Array['query'] . ' WHERE id=:id';

    }
    return $this->$methodName($query, $Query_Array['prepare']);
  }
  /**
   * Удаление записи по ID
   */
  protected function remove($id)
  {
    $query = 'DELETE FROM ' . Core::$DatabaseConfig['connection']['prefix'].$this->Entity->getTable()
          . ' WHERE id = :id';

    return $this->query($query, [
      ':id' => $id
    ]);
  }
  /**
   * Генерируем поля на вставку
   */
  private function generateInsertFields($Entity)
  {
    $EntityInfo = $this->Entity->getEntityInfo();
    
    $insertFields = '(';
    $insertValues = '(';
    $prepareResult = [];
    foreach ($EntityInfo['properties'] as $key => $value) {
      $methodName = 'get'.ucfirst($key);
      $property = $Entity->$methodName();
      $fieldRealName = EntityHandler::getFieldName($key, $value);
      $insertFields .= $fieldRealName . ',';
      $insertValues .= ':' . $fieldRealName . ',';
      $prepareResult[':' . $fieldRealName] = $property;
    }

    $insertFields = rtrim($insertFields, ',') . ')';
    $insertValues = rtrim($insertValues, ',') . ')';

    return [
      'query' => $insertFields . ' VALUES ' . $insertValues,
      'prepare' => $prepareResult
    ];
  }
  /**
   * Генерируем поля на обновление
   */
  private function generateUpdateFields($Entity)
  {
    $EntityInfo = $this->Entity->getEntityInfo();

    $updateFields = '';
    $prepareResult = [];

    foreach ($EntityInfo['properties'] as $key => $value) {
      $methodName = 'get'.ucfirst($key);
      $property = $Entity->$methodName();

      $fieldRealName = EntityHandler::getFieldName($key, $value);
      if ($fieldRealName == 'id') {
        $prepareResult[':' . $fieldRealName] = $property;
        continue;
      }

      if ($property != null) {
        $updateFields .= $fieldRealName . '=:' . $fieldRealName . ', ';
        $prepareResult[':' . $fieldRealName] = $property;
      }
    }

    $updateFields = rtrim($updateFields, ', ');

    return [
      'query' => $updateFields,
      'prepare' => $prepareResult
    ];
  }
  /**
   * Генерация запроса по имени метода
   *
   * @param array $query_array - Массив разбитого названия метода
   * @param array $params - Массив переданных в метод параметров
   *
   * @return array
   */
  private function generateQuery($query_array, $params)
  {
    // Приводим все элементы названия к нижнему регистру
    $query_array = array_map(function ($el) {
      return mb_strtolower($el);
    }, $query_array);

    for ($i = 0; $i < count($query_array); $i++) {
      if ($query_array[$i] == 'by') {
        if (isset($query_array[$i + 2]) 
          && $query_array[$i + 2] != 'or' 
          && $query_array[$i + 2] != 'and' 
          && $query_array[$i + 2] != 'asc' 
          && $query_array[$i + 2] != 'desc' 
          && $query_array[$i + 2] != 'limit' 
        ) {
          $query_array[$i + 1] = $query_array[$i + 1] . '_' . $query_array[$i + 2];
          unset($query_array[$i + 2]);
        }
      }
    }
    
    $query_array = array_values($query_array);
    
    // Все поля сущности
    $fields = $this->Entity->getFields();

    // Подготавливаем запрос и массив prepare
    $where = '';
    $order_by = '';
    $sort = '';
    $limit = '';
    $prepareResult = [];
    $paramsIterator = 0;
    $ordering = false;
    for ($i = 0; $i < count($query_array); $i++) {
      // Если среди свойств сущности находится элемент, добавляем его к запросу
      $propertyExists = array_search($query_array[$i], $fields);

      if ($propertyExists !== false && $ordering === false) {
        // SQL запрос
        $where .= ' '. $query_array[$i] .' = :'. $query_array[$i] . '_' . $paramsIterator . ' ';
        // Массив prepare
        $prepareResult[':'.$query_array[$i] . '_' . $paramsIterator] = $params[$paramsIterator];
        // Переходим к следующему параметру
        $paramsIterator++;
      }
      // Если есть слово and в элементе, добавляем его к SQL запросу
      if ($query_array[$i] == 'and') {
        $where.= ' AND ';
      }
      // Если есть слово or в элементе, добавляем его к SQL запросу
      if ($query_array[$i] == 'or') {
        $where.= ' OR ';
      }
      // Сортировка order_by
      if ($query_array[$i] == 'order') {
        if ($query_array[$i + 1] == 'by') {
          $order_by = ' ORDER BY '.$query_array[$i + 2];
          $ordering = true;
        }
      }
      // Сортировка desc
      if ($query_array[$i] == 'desc') {
        $sort = ' DESC ';
      }
      // Сортировка asc
      if ($query_array[$i] == 'asc') {
        $sort = ' ASC ';
      }
      // Ограничение на выборку записей
      if ($query_array[$i] == 'limit') {
        if (isset($params[$paramsIterator])) {
          $limit .= ' LIMIT '.$params[$paramsIterator];
          $paramsIterator++;

          if (isset($params[$paramsIterator])) {
            $limit .= ', '.$params[$paramsIterator];
            $paramsIterator++;
          }
        }

      }
    }
    // Выборка множества записей
    $multiple = false;
    if (array_search('all', $query_array)) {
      $multiple = true;
    }

    $query = 'SELECT '. implode(', ', $fields)
          . ' FROM ' . Core::$DatabaseConfig['connection']['prefix'] . $this->Entity->getTable()
          . ' WHERE '.$where.$order_by.$sort.$limit;

    return [
      'query' => $query,
      'prepare' => $prepareResult,
      'multiple' => $multiple
    ];
  }
  /**
   * Если при вызове нашего метода не существует, пытаемся создать запрос исходя из имени метода
   * Логика следующая:
   * Разделение происходи по заглавным буквам
   * Если в имени присутствует All, возвращаем все записи
   * Собираем параметры по Названиям findById (Id - название)
   * Если присутствует слово Desc, возвращаем данные в обратном порядке
   * Если присутствует слово OrderBy, после него идет название поля по которому сортировать
   */
  public function __call($method, $params)
  {
    if(method_exists($this, $method)) {
		  return call_user_func_array(array($this, $method), $params);
		} else {
      // Разбиваем название метода по большой букве
      $Method_Array = preg_split('/(?=\p{Lu})/u', lcfirst($method));
      // Строим SQL запрос и поля для prepare
      $Query_Array = $this->generateQuery($Method_Array, $params);
      // Выполняем SQL запрос
      $Query_Result = call_user_func_array(array($this, 'query'), [
        $Query_Array['query'],
        $Query_Array['prepare']
      ]);
      // Если присутствует All, возвращаем все записи, в других случаях возвращаем первую запись
      if ($Query_Array['multiple']) {
        return $Query_Result;
      } else {
        return $Query_Result[0];
      }
    }
  }

}
?>
