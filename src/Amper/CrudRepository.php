<?php
namespace Amper;

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
      $Method_Array = preg_split('/(?=\p{Lu})/u', lcfirst($method));
      print_r($Method_Array);
    }

    exit;
  }

}
?>
