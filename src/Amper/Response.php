<?php
namespace Amper;

class Response {

  private $meta;
  private $data;
  private $status;
  private $type;

  public function execute()
  {
    if ($this->type == 'JSON') {
      $Result = [];
      if ($this->status) http_response_code($this->status);
      if ($this->meta) $Result['meta'] = $this->meta;
      if ($this->data) $Result['data'] = $this->data;
      echo json_encode($Result,JSON_UNESCAPED_UNICODE);
    }
  }

  public function toJson() : Response
  {
    $this->type = 'JSON';
    return $this;
  }

  public function getType() : string
  {
    return $this->type;
  }

  public function setMeta(array $data) : Response
  {
    $this->meta = $data;
    return $this;
  }

  public function getMeta() : array
  {
    return $this->meta;
  }

  public function setData(array $data) : Response
  {
    $this->data = $data;
    return $this;
  }

  public function getData() : array
  {
    return $this->data;
  }

  public function setStatus(int $data) : Response
  {
    $this->status = $data;
    return $this;
  }

  public function getStatus() : number
  {
    return $this->status;
  }

}
?>
