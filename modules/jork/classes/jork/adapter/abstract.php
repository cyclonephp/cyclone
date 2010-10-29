<?php

/**
 * Base class for all JORK adapters
 */
abstract class JORK_Adapter_Abstract implements JORK_Adapter {

    protected $db;

    public function  __construct(DB_Adapter $db) {
        $this->db = $db;
    }

    /**
     *
     * @param JORK_Query_Select $jork_select
     * @return JORK_Query_Result
     */
    public function  exec_select(JORK_Query_Select $jork_select) {
        $db_select = $this->map_select($jork_select);
        $db_result = $this->db->exec_select($db_select);
        return $this->map_select_result($db_result, $jork_select);
    }

    /**
     * @return DB_Query_Select
     */
    abstract function map_select(JORK_Query_Select $select);

    /**
     * @return JORK_Query_Result
     */
    abstract function map_select_result(DB_Query_Result $db_result
            , JORK_Query_Select $jork_select);
    

    /**
     * Helper function.
     *
     * Maps the optionally aliased entity name to an array that contains the
     * table name (string) or an array which containes the table name and the
     * alias.
     *
     * @param string $entity_name the entity name and an optional alias separated by a space character
     * @return array
     */
    protected static function entity2table($entity_name) {
        $entity_parts = split(' ', $entity_name);

        switch(count($entity_parts)) {
            case 2: $entity_alias = $entity_parts[1];
            case 1: $entity_class = $entity_parts[0]; break;
            default: throw new JORK_Exception('invalid entity: '.$entity_name);
        }

        $table_name = JORK::schema($entity_class)->table;

        return array(isset($entity_alias)
            ? array($table_name, $entity_alias) : $table_name);
    }

    protected static function property_chain2joins($root_entity_class, $jork_joins) {
        $db_joins = array();

        $parts = split(' ', $root_entity_class);
        $part_count = count($parts);

        $entity_name = $parts[0];

        $schema = JORK::schema($entity_name);

        if (1 == $part_count) {
            $table_name = $table_alias = $schema->table;
        } elseif (2 == $part_count) {
            $table_name = $schema->table;
            $table_alias = $parts[1];
        } else
            throw new JORK_Exception('invalid root entity');
        
        foreach ($jork_joins as $join) {
            $property_chain = explode('.', $join['component_path']);
            foreach ($property_chain as $component) {
                if ( ! array_key_exists($component, $schema->components))
                    throw new JORK_Exception('component '.$component.' of class '.$entity_name.' does not exist');

                self::component2join($entity_name
                    , $table_alias
                    , $component
                    , &$db_joins);

                $entity_name = $schema->components[$component]['class'];
                $schema = JORK::schema($entity_name);
            }
        }

        return $db_joins;
    }

    protected static function component2join($class, $table, $component, &$db_join_arr) {
        $schema = JORK::schema($class);
        $comp_def = $schema->components[$component];

        $remote_schema = JORK::schema($comp_def['class']);

        if (JORK::ONE_TO_MANY == $comp_def['type']) {
            $db_join = array(
                'table' => $remote_schema->table,
                'type' => 'INNER',
                'conditions' => array(
                     array($table.'.'.$schema->primary_key(), '=',
                        $remote_schema->table.'.'.$comp_def['join_column'])
                )
            );
            $db_join_arr []= $db_join;
        } elseif (JORK::MANY_TO_ONE == $comp_def['type']) {
            $db_join_arr []= array(
                'table' => $remote_schema->table,
                'type' => 'INNER',
                'conditions' => array(
                    array($schema->table.'.'.$comp_def['join_column'], '=',
                        $remote_schema->table.'.'.$remote_schema->primary_key())
                )
            );
        }
    }

}