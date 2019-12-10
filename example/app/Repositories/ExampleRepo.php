<?php
namespace App\Repositories;

use Amper\CrudRepository;
use \App\Entities\ExampleEntity;

class ExampleRepo extends CrudRepository {

  public function __construct()
  {
    parent::__construct(ExampleEntity::class);
  }

}