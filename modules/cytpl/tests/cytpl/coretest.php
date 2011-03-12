<?php

class CyTpl_CoreTest extends Kohana_Unittest_TestCase {

    private function html($tpl) {
        $compiler = CyTpl_Compiler::for_template($tpl);
        return $compiler->compile();
    }

    /**
     * @param string $tpl
     * @return CyTpl_Compiler
     */
    private function compile($tpl) {
        $compiler = CyTpl_Compiler::for_template($tpl);
        $compiler->compile();
        return $compiler;
    }


    /**
     * @dataProvider providerCoreCommands
     */
    public function testCoreCommands($tpl, $html) {
        $compiler = CyTpl_Compiler::for_template($tpl);
        $result = $compiler->compile();
        $this->assertEquals($html, $result);
    }

    public function providerCoreCommands() {
        return array(
            'echo' => array('{$name}', '<?php echo $name?>'),
            'foreach' => array('{foreach $users as $user}', '<?php foreach ($users as $user) : ?>'),
            'endforeach' => array('{/foreach}', '<?php endforeach; ?>'),
            'foreachkey' => array('{foreach $arr as $k => $v}', '<?php foreach ($arr as $k => $v) : ?>'),
            'if' => array('{if $a == 2}', '<?php if ($a == 2) : ?>'),
            'elif' => array('{elif $a == 2}', '<?php elseif ($a == 2) : ?>'),
            'elseif' => array('{elseif $a == 2}', '<?php elseif ($a == 2) : ?>'),
            'else if' => array('{else if $a == 2}', '<?php elseif ($a == 2) : ?>')
        );
    }

    /**
     * @expectedException CyTpl_Template_Exception
     */
    public function testNamespaceDeclaration() {
        $compiler = $this->compile('{use hyperform:h}
            {use another}');
        $this->assertEquals(array(
            'h' => 'hyperform',
            'another' => 'another'
        ), $compiler->get_namespaces());

        $this->compile('{use hyperform:h:x}');
    }

    /**
     * @expectedException CyTpl_Template_Exception
     * @expectedExceptionMessage invalid namespace 'h' in command 'h:input'
     */
    public function testNamespacedCommand() {
        $compiler = new CyTpl_Compiler('');
        $compiler->extract_namespace('use hyperform:h');
        $compiler->extract_namespace('use another');
        $compiler->get_namespaced_command('h:input');

        
        $compiler = new CyTpl_Compiler('');
        $command = $compiler->get_namespaced_command('h:input');
    }

}