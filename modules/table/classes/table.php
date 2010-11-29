<?php


class Table {

    /**
     * @var array
     */
    public $model;

    public function  __construct($model) {
        if (is_array($model)) {
            $this->model = $model;
        } else {
            $file = Kohana::find_file('tables', $model);
            if (FALSE == $file)
                throw new Table_Exception ('unknown table model: '.$model);
            $this->model = require $file;
        }
    }
}