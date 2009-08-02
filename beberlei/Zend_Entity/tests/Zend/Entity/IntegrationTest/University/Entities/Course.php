<?php

class ZendEntity_Course implements Zend_Entity_Interface
{
    public $id;

    public $name;

    public $teacher;

    public function getState()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'teacher' => $this->teacher,
        );
    }

    public function setState(array $state)
    {
        foreach($state AS $k => $v) {
            $this->$k = $v;
        }
    }
}