<?php


class JORK_Mapping_SchemaTest extends Kohana_Unittest_TestCase {

    /**
     * @expectedException JORK_Schema_Exception
     */
    public function testGetPropSchema() {
        $schema = JORK_Model_Abstract::schema_by_class('Model_User');
        $this->assertEquals($schema->get_property_schema('id'), array(
                'type' => 'int',
                'primary' => true,
                'geneneration_strategy' => 'auto'
            ));

        $this->assertEquals($schema->get_property_schema('posts'), array(
                'class' => 'Model_Post',
                'type' => JORK::ONE_TO_MANY,
                'join_column' => 'user_fk'
            ));
        $schema->get_property_schema('dummy');
    }
}