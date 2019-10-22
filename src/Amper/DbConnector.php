<?php
namespace Amper;

use \PDO;

class DbConnector {

  private $PDO;

  public function __construct()
  {
    try {
			$this->PDO = new PDO(
				Core::$DatabaseConfig['connection']['driver'].':host='
        . Core::$DatabaseConfig['connection']['host']
        . ';dbname=' . Core::$DatabaseConfig['connection']['name'],
				Core::$DatabaseConfig['connection']['user'],
				Core::$DatabaseConfig['connection']['password']
			);
			$this->PDO->query('SET NAMES utf8');
		} catch (PDOException $Exception) {
			var_dump($Exception->getMessage());
      exit;
		}
  }

  /**
   * Запрос к БД
   */
  public function query(string $query, $prepare = [])
  {
    $handle = $this->PDO->prepare($query);
		$handle->execute($prepare);
		$result = [];
		$i = 0;
		while ($row = $handle->fetch(PDO::FETCH_ASSOC)) {
			$result[$i] = $row;
			$i++;
		}
		return $result;
  }

  public function __destruct() {
		$this->PDO = null;
	}
}

?>
