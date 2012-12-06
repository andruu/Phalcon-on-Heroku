<?php 
class Posts extends \Phalcon\Mvc\Model {
  public function initialize () {
    $this->hasMany("id", "Comments", "post_id");
  } 
}