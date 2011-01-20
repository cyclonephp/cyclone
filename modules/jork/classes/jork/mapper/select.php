<?php

/**
 * Maps a jork select to a db select.
 */
class JORK_Mapper_Select {

    /**
     * @var JORK_Query_Select
     */
    protected $_jork_query;

    /**
     * @var DB_Query_Select
     */
    protected $_db_query;

    /**
     * @var array<JORK_Mapper_Result>
     */
    protected $_mappers;

    /**
     * @var JORK_Naming_Service
     */
    protected $_naming_srv;

    /**
     * @var boolean
     */
    public $has_implicit_root;

    /**
     * @var JORK_Mapping_Schema
     */
    protected $_implicit_root;

    public function  __construct(JORK_Query_Select $jork_query) {
        $this->_jork_query = $jork_query;
        $this->_db_query = new DB_Query_Select;
        $this->_naming_srv = new JORK_Naming_Service;
    }

    public function map() {
        if (count($this->_jork_query->from_list) == 1
                &&  ! array_key_exists('alias', $this->_jork_query->from_list[0])) {
            $this->has_implicit_root = TRUE;
            $impl_root_class = $this->_jork_query->from_list[0]['class'];
            $this->_implicit_root = JORK_Model_Abstract::schema_by_class($impl_root_class);
            $this->_naming_srv->set_implicit_root($impl_root_class);
        }

        $this->map_from();

        $this->map_join();

        $this->map_with();

        $this->map_select();

        $this->map_where();

        return array($this->_db_query, $this->_mappers);
    }

    protected function create_entity_mapper($select_item) {
        return new JORK_Mapper_Entity($this->_naming_srv
                , $this->_jork_query
                , $this->_db_query
                , $select_item);
    }

    protected function map_from() {
        if ($this->has_implicit_root) {
            $this->_mappers[NULL] = $this->create_entity_mapper(NULL);
        } else {
            foreach ($this->_jork_query->from_list as $from_item) {
                //fail early
                if ( ! array_key_exists('alias', $from_item))
                        throw new JORK_Syntax_Exception('if the query hasn\'t got an
                            implicit root entity, then all explicit root entities must
                            have an alias name');

                $this->_naming_srv->set_alias($from_item['class'], $from_item['alias']);
                $this->_mappers[$from_item['alias']] =
                        $this->create_entity_mapper($from_item['alias']);

            }
        }
    }

    protected function map_join() {
        
    }

    protected function map_with() {
        foreach ($this->_jork_query->with_list as $with_item) {
            if (array_key_exists('alias', $with_item)) {
                $this->_naming_srv->set_alias($with_item['prop_chain'], $with_item['alias']);
            }
            if ($this->has_implicit_root) {
                $this->_mappers[NULL]->merge_prop_chain($with_item['prop_chain']->as_array(), TRUE, TRUE);
            } else {
                $prop_chain = $with_item->as_array();
                $root_entity = array_shift($prop_chain);
                if ( ! array_key_exists($root_entity, $this->_mappers))
                    throw new JORK_Syntax_Exception('invalid root entity in WITH clause: '.$root_entity);

                $this->_mappers[$root_entity]->merge_prop_chain($prop_chain, TRUE, TRUE);
            }
        }
    }

    /**
     * Resolves a custom database expression passed as string.
     *
     * Picks property chains it founds in enclosing brackets, resolves the
     * property chains to table names. If the last item is an atomic property
     * then it puts the coresponding table column to the resolved expression,
     * otherwise throws an exception
     *
     * @param <type> $expr
     * @return string
     */
    protected function map_db_expression($expr) {
        $pattern = '/\{([^\}]*)\}/';
        preg_match_all($pattern, $expr, $matches);
        $resolved_expr_all = $expr;
        foreach ($matches[0] as $idx => $match) {
            $prop_chain = JORK_Query_PropChain::from_string($matches[1][$idx]);
            $prop_chain_arr = $prop_chain->as_array();
            if ($this->has_implicit_root) {
                $resolved_expr = $this->_mappers[NULL]->resolve_prop_chain($prop_chain_arr);
            } else {
                $root_prop = array_shift($prop_chain_arr);
                $resolved_expr = $this->_mappers[$root_prop]
                        ->resolve_prop_chain($prop_chain_arr);
                if (is_array($resolved_expr))
                    throw new JORK_Exception('invalid property chain in database expression \''.$expr.'\'');
            }
            $resolved_expr_all = str_replace($match, $resolved_expr, $resolved_expr_all);
        }
        return $resolved_expr_all;
    }

    /**
     * Maps the SELECT clause of the jork query to the db query.
     *
     * @see JORK_Mapper_Select::$_jork_query
     * @see JORK_Mapper_Select::$_db_query
     * @return void
     */
    protected function map_select() {
        if (empty($this->_jork_query->select_list)) {
            foreach ($this->_mappers as $mapper) {
                $mapper->select_all_atomics();
            }
            return;
        }
        foreach ($this->_jork_query->select_list as $select_item) {
            if (array_key_exists('expr', $select_item)) { //database expression
                $resolved = $this->map_db_expression($select_item['expr']);
                $this->_mappers[$select_item['expr']] = new JORK_Mapper_Expression($resolved);
                continue;
            }
            $prop_chain = $select_item['prop_chain']->as_array();
            if ($this->has_implicit_root) {
                $this->_mappers[NULL]->merge_prop_chain($prop_chain, TRUE);
            } else {
                $root_entity = array_shift($prop_chain);
                if ( ! array_key_exists($root_entity, $this->_mappers))
                    throw new JORK_Syntax_Exception('invalid property chain in select clause:'
                            .$select_item['prop_chain']->as_string());
                if (empty($prop_chain)) {
                    $this->_mappers[$root_entity]->select_all_atomics();
                } else {
                    $this->_mappers[$root_entity]->merge_prop_chain($prop_chain, TRUE);
                }
            }
            if (array_key_exists('projection', $select_item)) {
                $this->add_projections($select_item['prop_chain'], $select_item['projection']);
            }
        }
    }

    protected function add_projections(JORK_Query_PropChain $prop_chain, $projections) {
        
    }

    /**
     * Resolves any kind of database expressions, takes operands as property
     * chains, replaces them with the corresponding table aliases and column names
     * and merges the property chains.
     *
     * @param DB_Expression $expr
     * @return DB_Expression
     */
    protected function resolve_db_expr(DB_Expression $expr) {
        $is_binary = FALSE;
        if ($this->has_implicit_root) {
            if ($expr instanceof DB_Expression_Binary) {
                $is_binary = TRUE;
                if ($expr->left_operand instanceof DB_Expression) {
                    $expr->left_operand = $this->resolve_db_expr($expr->left_operand);
                } else {
                    $expr->left_operand = $this->_mappers[NULL]
                        ->resolve_prop_chain(explode('.', $expr->left_operand));
                }

                if ($expr->right_operand instanceof DB_Expression) {
                    $expr->right_operand = $this->resolve_db_expr($expr->right_operand);
                } else {
                    $expr->right_operand = $this->_mappers[NULL]
                        ->resolve_prop_chain(explode('.', $expr->right_operand));
                }
            } elseif ($expr instanceof DB_Expression_Unary) {
                $expr->operand = $this->_mappers[NULL]->resolve_prop_chain(explode('.', $expr->operand));
            } elseif ($expr instanceof DB_Expression_Custom) {
                $expr->str = $this->map_db_expression($expr->str);
            }
        } else {
            if ($expr instanceof DB_Expression_Binary) {
                $is_binary = TRUE;
                if ($expr->left_operand instanceof DB_Expression) {
                    $expr->left_operand = $this->resolve_db_expr($expr->left_operand);
                } else {
                    $left_prop_chain = explode('.', $expr->left_operand);
                    $left_root_prop = array_shift($left_prop_chain);
                    $expr->left_operand = $this->_mappers[$left_root_prop]->resolve_prop_chain($left_prop_chain);
                }
                if ($expr->right_operand instanceof DB_Expression) {
                    $expr->right_operand = $this->resolve_db_expr($expr->right_operand);
                } else {
                    $right_prop_chain = explode('.', $expr->right_operand);
                    $right_root_prop = array_shift($right_prop_chain);
                    $expr->right_operand = $this->_mappers[$right_root_prop]->resolve_prop_chain($right_prop_chain);
                }
            } elseif ($expr instanceof DB_Expression_Unary) {
                $prop_chain = explode('.', $expr->operand);
                $root_prop = array_shift($prop_chain);
                $expr->operand = $this->_mappers[$root_prop]->resolve_prop_chain($prop_chain);
            } elseif ($expr instanceof DB_Expression_Custom) {
                $expr->str = $this->map_db_expression($expr->str);
            }
        }
        if ($is_binary) {
            $this->obj2condition($expr);
        }
        return $expr;
    }

    protected function obj2condition(DB_Expression_Binary $expr) {
        if (is_array($expr->left_operand) && $expr->operator == '='
                && is_array($expr->right_operand)) {
            //TODO resolving object equality check to primary key equality checks
            list($left_mapper, $left_ent_schema, $left_last_prop)
                    = $expr->left_operand;
            list($right_mapper, $right_ent_schema, $right_last_prop)
                    = $expr->right_operand;
            if ($left_ent_schema->components[$left_last_prop]['class']
                    != $right_ent_schema->components[$right_last_prop]['class'])
                throw new JORK_Exception("unable to check equality of class '"
                        . $left_ent_schema->components[$left_last_prop]['class'] . "' with class '"
                        . $right_ent_schema->components[$right_last_prop]['class'] . "'");
            //holy shit... it's coming -.-
            $left_mapper->merge_prop_chain(array($left_last_prop
                , JORK_Model_Abstract::schema_by_class($left_ent_schema->components[$left_last_prop]['class'])->primary_key()));
            $right_mapper->merge_prop_chain(array($right_last_prop
                , JORK_Model_Abstract::schema_by_class($right_ent_schema->components[$right_last_prop]['class'])->primary_key()));
        } elseif (is_array($expr->left_operand) || is_array($expr->right_operand))
        //only one operand was an array
            throw new JORK_Exception();
    }

    /**
     * Maps the where clause of the jork query
     *
     * @see JORK_Mapper_Select::$_jork_query
     * @see JORK_Mapper_Select::$_db_query
     * @see JORK_Mapper_Select::resolve_db_expr()
     */
    protected function map_where() {
        foreach ($this->_jork_query->where_conditions as $cond) {
            $this->_db_query->where_conditions []= $this->resolve_db_expr($cond);
        }
    }

}