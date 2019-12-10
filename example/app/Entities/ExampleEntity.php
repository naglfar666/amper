<?php
namespace App\Entities;

use \Amper\Entity;
/**
 * @Table : name=example
 */
class ExampleEntity extends Entity {
  /**
   * @Id
   * @GeneratedValue : strategy=AUTO_INCREMENT
   * @Field : name=id, type=int, length=11
   * @NotNull
   * @var integer
   */
  private $id;
  /**
   * @NotNull
   * @Field : name=keyname, type=varchar, length=255
   * @var string
   */
  private $keyname;
  /**
   * @Nullable
   * @Field : name=value, type=varchar, length=255
   * @var string
   */
  private $value;

  /**
   * @NotNull
   * @Field: name=date_add, type=int, length=11
   * @var integer
   */
  private $dateAdd;

  /**
   * @return int
   */
  public function getId(): int
  {
    return $this->id;
  }

  /**
   * @param int $id
   */
  public function setId(int $id): void
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getKeyname(): string
  {
    return $this->keyname;
  }

  /**
   * @param string $keyname
   */
  public function setKeyname(string $keyname): void
  {
    $this->keyname = $keyname;
  }

  /**
   * @return string
   */
  public function getValue(): string
  {
    return $this->value;
  }

  /**
   * @param string $value
   */
  public function setValue(string $value): void
  {
    $this->value = $value;
  }

  /**
   * @return int
   */
  public function getDateAdd(): int
  {
    return $this->dateAdd;
  }

  /**
   * @param int $dateAdd
   */
  public function setDateAdd(int $dateAdd): void
  {
    $this->dateAdd = $dateAdd;
  }

}