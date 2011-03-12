<?php

class CyTpl_Compiler {


    public static function for_template($tpl) {
        return new CyTpl_Compiler($tpl);
    }

    private $_tpl;

    private $_namespaces = array();

    public function get_namespaces() {
        return $this->_namespaces;
    }

    public function  __construct($tpl) {
        $this->_tpl = $tpl;
    }

    public function extract_namespace($command) {
        if (preg_match('/^use (?P<namespace>.+)$/', $command, $matches)) {
            $namespace = $matches['namespace'];
            $segments = explode(':', $namespace);
            $count = count($segments);
            if ($count == 1) {
                $this->_namespaces [$segments[0]]= $segments[0];
            } elseif ($count == 2) {
                $this->_namespaces[$segments[1]] = $segments[0];
            } else
                throw new CyTpl_Template_Exception("invalid command: $command");
            return TRUE;
        }
        return FALSE;
    }

    public function get_namespaced_command($command) {
        if (preg_match('/^(?P<ns_cmd>([a-zA-Z_-]+\:[a-zA-Z_-]+))/', $command, $matches)) {
            $raw_str = $matches['ns_cmd'];
            list($namespace, $cmd) = explode(':', $raw_str);
            if (!isset($this->_namespaces[$namespace]))
                throw new CyTpl_Template_Exception("invalid namespace '$namespace' in command '$command'");

            $raw_arg_list = substr($command, strlen($raw_str));
            $raw_args = explode(',', $raw_arg_list);
            $arguments = array();
            if ( ! (count($raw_args) == 1 && $raw_args[0] == '')) {
                foreach ($raw_args as $raw_arg) {
                    $segments = explode('=', $raw_arg);
                    $count = count($segments);
                    if (1 == $count) {
                        $arguments [] = $segments[0];
                    } elseif (2 == $count) {
                        $arguments[$segments[0]] = $segments[1];
                    } else
                        throw new CyTpl_Template_Exception("invalid argument $raw_arg in command $command");
                }
            }
            
            return CyTpl_Command::factory($this->_namespaces[$namespace]
                    , $cmd, $arguments);
        }
        return NULL;
    }

    public function compile() {
        preg_match_all('/\{(?P<match>[^\}]+)\}/', $this->_tpl, $matches);
        foreach ($matches[0] as $idx => $m) {
            $command = $matches['match'][$idx];
            if ($this->extract_namespace($command))
                continue;

            $ns_command = $this->get_namespaced_command($command);
            if ( ! is_null($ns_command)) {
                
                continue;
            }
            
            $compiled = $this->compile_core_command($command);
            $this->_tpl = str_replace('{' . $command . '}', $compiled, $this->_tpl);
        }
        return $this->_tpl;
    }

    private function compile_core_command($command) {
        $command = trim($command);
        if ($command{0} == '$') {
            return '<?php echo $' . substr($command, 1) . '?>';
        }

        if ($command == 'endif' || $command == '/if' || $command == 'fi') {
            return '<?php endif; ?>';
        }

        if (substr($command, 0, 2) == 'if') {
            preg_match_all('/if (?P<condition>.*)/', $command, $matches);
            $condition = $matches['condition'][0];
            
            if (preg_match_all('/exists (?P<var>[^\[ ]+)(\[(?P<key>.*)\])?/', $condition, $matches)) {
                if ($matches['key'][0] == '') {
                    return '<?php if (isset('.$matches['var'][0].')) : ?>';
                } else {
                    $key = $matches['key'][0];
                    if ($key{0} != '$') {
                        $key = "'$key'";
                    }
                    return '<?php if (array_key_exists(' . $key . ', ' . $matches['var'][0] . ')) : ?>';
                }
            }
            return '<?php if (' . $condition . ') : ?>';
        }

        foreach (array('elif', 'elsif', 'elseif', 'else if') as $elif_command) {
            if (preg_match_all('/' . $elif_command . ' (?P<condition>.*)/', $command, $matches)) {
                return '<?php elseif ('. $matches['condition'][0] . ') : ?>';
            }
        }
        
        if (substr($command, 0, 4) == 'else') {
            return '<?php else : ?>';
        }
        if (substr($command, 0, 5) == 'attrs') {
            $attrs = substr($command, 5, strlen($command));
            return '<?php foreach (' . $attrs . ' as $k => $v) echo \' \' . $k . \'="\' . $v . \'"\'; ?>';
        }

        if (substr($command, 0, 7) == 'foreach') {
            if (preg_match_all('/foreach (?P<arr>.+) as (?P<itm>[^ ]+)$/', $command, $matches)) {
                return '<?php foreach (' . $matches['arr'][0] . ' as '. $matches['itm'][0] . ') : ?>';
            }
            if (preg_match_all('/foreach (?P<arr>.+) as (?P<key>[^ ]+) => (?P<val>[^ ]+)$/', $command, $matches)) {
                return '<?php foreach (' . $matches['arr'][0] 
                    . ' as '. $matches['key'][0] . ' => '. $matches['val'][0] . ') : ?>';
            }
        }

        if (in_array($command, array('/foreach'))) {
            return '<?php endforeach; ?>';
        }
    }

    
}