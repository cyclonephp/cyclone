<?php


class JORK_Model_Collection_Iterator extends ArrayIterator {

    public function  current() {
        $rval = parent::current();
        return $rval['value'];
    }

}