<?php

class JORK_Alias_Factory {

    protected $aliases = array();

    public function for_table($table_name) {
        if ( !array_key_exists($table_name, $this->aliases)) {
            $this->aliases[$table_name] = 0;
        }
        return $table_name.'_'.(++$this->aliases[$table_name]);
    }

    public static function entitydef_segments($entitydef) {
        $segments = explode(' ', $entitydef);
        if (count($segments) == 1) {
            $alias = $segments[0];
        } elseif (count($segments) == 2) {
            $alias = $segments[1];
        } else
            throw new JORK_Exception("invalid entity: '$entitydef'");
        $entity_class = $segments[0];

        return array($entity_class, $alias);
    }

}