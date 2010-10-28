<?php

class Controller_Record extends Controller {

    private $forced = false;

    public function action_index() {
        if ( ! Kohana::$is_cli)
            die();
        
        $scope = 'classes'.DIRECTORY_SEPARATOR.'record';
        if (($param = $this->request->param('id'))) {
            $classes = explode(',', $param);
        } else {
            $classes = $this->parse_classnames(Kohana::list_files($scope));
        }
        $error_count = 0;
        foreach ($classes as $class) {
            if ($class != 'Record_Base') {
                $error_count += $this->generate_schema($class);
            }
        }
        echo "\nDDL execution completed with $error_count errors.\n";
    }

    public function action_forced() {
        $this->forced = true;
        $this->action_index();
    }

    private function parse_classnames(array $files) {
        $rval = array();
        foreach ($files as $rel => $abs) {
            if (is_array($abs)) {
                $rval += $this->parse_classnames($abs);
            } else {
                $rel_path = substr($rel, strlen('classes/'), strlen($rel) - strlen('classes/.php'));
                $tmp = explode(DIRECTORY_SEPARATOR, $rel_path);
                $rel_path = '';
                $first = true;
                foreach ($tmp as $segment) {
                    if ( ! $first) {
                        $rel_path .= '_';
                    } else {
                        $first = false;
                    }
                    $rel_path .= ucfirst($segment);
                }
                $rval [$rel]= $rel_path;
            }
        }
        return $rval;
    }

    private function generate_schema($class) {
        $obj = new $class;
        $schema = $obj->get_schema_definition();
        $cfg = Kohana::config('database.'.$schema['database']);
        $cfg = $cfg['connection'];
        mysql_connect($cfg['hostname'], $cfg['username'], $cfg['password']) and
                mysql_select_db($cfg['database']) or die('failed to connect');

        $first = true;
        $sql = "CREATE TABLE `{$schema['table_name']}` (";
        foreach ($schema['columns'] as $name => $def) {
            if ( ! $first) {
                $sql .= ',';
            } else {
                $first = false;
            }
            $sql .= "\n\t`$name` $def";
        }
        $sql .= "\n)\n";
        for (;;) {
            mysql_query($sql);
            $err = mysql_error();
            if ($err) {
                if ( ! $this->forced || substr($err, strlen($err) - strlen('already exists')) != 'already exists') {
                    echo $sql;
                    echo $err."\n";
                    return 1;
                } else {
                    mysql_query('DROP TABLE '.$schema['table_name']);
                    continue;
                }
            } else {
                break;
            }
        }
        return 0;
    }

}