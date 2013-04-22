<?php

use cyclone\autoloader;

class Core_Autoloader_NamespacedTest extends Kohana_Unittest_TestCase {

    public function testListClasses() {
        $classes = autoloader\Namespaced::inst()->list_classes('cyclone\\config');
        $expected = array(
            'cyclone\\config\\reader\\DatabaseReader',
            'cyclone\\config\\reader\\FileReader',
            'cyclone\\config\\reader\\FileEnvReader',
            'cyclone\\config\\writer\\DatabaseWriter',
            'cyclone\\config\\CycloneException',
            'cyclone\\config\\MockStorage',
            'cyclone\\config\\Reader',
            'cyclone\\config\\Writer',
        );
        $this->assertEquals(count($expected), count($classes), 'proper listed class count');
        foreach ($expected as $exp_class) {
            $this->assertTrue(in_array($exp_class, $classes), "$exp_class listed");
        }
    }
    
}