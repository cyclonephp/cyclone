<?php


class JORK_Mapper_Select_ExplRoot extends JORK_Mapper_Select {

    protected function map_from() {
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

    protected function map_with() {
        foreach ($this->_jork_query->with_list as $with_item) {
            $prop_chain = $with_item->as_array();
                $root_entity = array_shift($prop_chain);
                if ( ! array_key_exists($root_entity, $this->_mappers))
                    throw new JORK_Syntax_Exception('invalid root entity in WITH clause: '.$root_entity);

                $this->_mappers[$root_entity]->merge_prop_chain($prop_chain, TRUE, TRUE);
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
            $root_prop = array_shift($prop_chain_arr);
            $resolved_expr = $this->_mappers[$root_prop]
                    ->resolve_prop_chain($prop_chain_arr);
            if (is_array($resolved_expr))
                throw new JORK_Exception('invalid property chain in database expression \''.$expr.'\'');
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
            $root_entity = array_shift($prop_chain);
            if ( ! array_key_exists($root_entity, $this->_mappers))
                throw new JORK_Syntax_Exception('invalid property chain in select clause:'
                        . $select_item['prop_chain']->as_string());
            if (empty($prop_chain)) {
                $this->_mappers[$root_entity]->select_all_atomics();
            } else {
                $this->_mappers[$root_entity]->merge_prop_chain($prop_chain, TRUE);
            }
            if (array_key_exists('projection', $select_item)) {
                $this->add_projections($select_item['prop_chain'], $select_item['projection']);
            }
        }
    }

    protected function add_projections(JORK_Query_PropChain $prop_chain, $projections) {
        $prop_chain_arr = $prop_chain->as_array();
        $root_prop = array_shift($prop_chain_arr);
        list($mapper,, $last_prop) = $this->_mappers[$root_prop]->resolve_prop_chain($prop_chain_arr);
        foreach ($projections as $raw_projection) {
            $proj = explode('.', $raw_projection);
            array_unshift($proj, $last_prop);
            $mapper->merge_prop_chain($proj);
        }
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
        if ($expr instanceof DB_Expression_Binary) {
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
            $this->obj2condition($expr);
        } elseif ($expr instanceof DB_Expression_Unary) {
            $prop_chain = explode('.', $expr->operand);
            $root_prop = array_shift($prop_chain);
            $expr->operand = $this->_mappers[$root_prop]->resolve_prop_chain($prop_chain);
        } elseif ($expr instanceof DB_Expression_Custom) {
            $expr->str = $this->map_db_expression($expr->str);
        }
        return $expr;
    }


    protected function map_order_by() {
        if ($this->_jork_query->order_by === NULL)
            return;

        foreach ($this->_jork_query->order_by as $ord) {
            $col_arr = explode('.', $ord['column']);
            $root_prop = array_shift($col_arr);
            $col = $this->_mappers[$root_prop]->resolve_prop_chain($col_arr);
            if (is_array($col))
                throw new JORK_Exception($ord['column'] . ' is not an atomic property');
            $this->_db_query->order_by [] = array(
                'column' => $col,
                'direction' => $ord['direction']
            );
        }
    }

    protected function  map_group_by() {
        
    }

    
}