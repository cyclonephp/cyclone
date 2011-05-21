<?php

class DB_Schema_Builder {

    /**
     * Entry point of SimpleDB CLI schema generation.
     *
     * @param array $args command-line arguments
     */
    public static function build_schema($args) {
        $forced = array_key_exists('--forced', $args) ? $args['--forced'] : FALSE;
        $suppress_execution = array_key_exists('--suppress-execution', $args)
                ? $args['--suppress-execution'] : FALSE;
        $module = array_key_exists('--module', $args) ? $args['--module'] : NULL;
        $builder = new DB_Schema_Builder($forced, $suppress_execution, $module);
        $builder->build();
    }

    public function ddl_for_table(DB_Schema_Table $table) {
        $generator = DB::schema_generator($table->database);
        $ddl = $generator->ddl_create_table($table, $this->_forced);
        if ( ! $this->_suppress_execution) {
            DB::executor($table->database)->exec_custom($ddl);
        }
        return $ddl;
    }

    private $_module;

    /**
     * @var boolean
     */
    private $_forced = FALSE;

    /**
     * @var boolean
     */
    private $_suppress_execution = FALSE;

    public function  __construct($forced = FALSE, $suppress_execution = FALSE, $module = NULL) {
        $this->_module = $module;
        $this->_forced = $forced;
        $this->_suppress_execution = $suppress_execution;
    }

    public function build() {
        $modules = NULL === $this->_module ? NULL : explode(',', $this->_module);
        $files = FileSystem::list_directory('classes/record', $modules);
        $classes = array();
        foreach ($files as $rel_path => $abs_path) {
            $prefix_len = strlen('classes/record') + 1;
            $relname = substr($rel_path, $prefix_len, strrpos($rel_path, '.') - $prefix_len);
            $classname = str_replace(DIRECTORY_SEPARATOR, '_', $relname);
            $classname = 'Record_' . $classname;
            if ($classname == 'Record_abstract' || $classname == 'Record_schema')
                continue;
            $classes []= $classname;
        }
        $ddl = '';
        foreach ($classes as $class) {
            $ddl .= $this->build_ddl_for_record($class);
        }
        if ($this->_suppress_execution) {
            echo $ddl;
        }
    }

    public function build_ddl_for_record($class) {
        $inst = call_user_func(array($class, 'inst'));
        $record_schema = $inst->schema();
        $table_schema = DB_Schema_Table::for_record_schema($record_schema);
        return $this->ddl_for_table($table_schema);
    }

}