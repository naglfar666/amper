<?php
namespace Amper;

class CrudRepository {

  private $Entity;

  public function __construct(string $entity)
  {
    $this->Entity = new $entity;

    // var_dump($this->Entity->getTable());
    // exit;
  }

}
?>
