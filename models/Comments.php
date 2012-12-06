<?php
class Comments extends \Phalcon\Mvc\Model {
  public function initialize () {
    $this->belongsTo("post_id", "Posts", "id");
  } 
}