<?php

class CyTpl_Compiler {


    public static function for_template($tpl) {
        return new CyTpl_Compiler($tpl);
    }

    private $_tpl;

    protected function  __construct($tpl) {
        $this->_tpl = $tpl;
    }

    public function compile() {
        preg_match_all('/\{(?P<match>[^\}]+)\}/', $this->_tpl, $matches);
        print_r($matches);
        foreach ($matches[0] as $idx => $m) {
            $command = $matches['match'][$idx];
            $compiled = $this->compile_command($command);
            $this->_tpl = str_replace('{' . $command . '}', $compiled, $this->_tpl);
        }
        return $this->_tpl;
    }

    private function compile_command($command) {
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