<?php


class KForm_Test extends Kohana_Unittest_TestCase {

    /**
     * @expectedException KForm_Exception
     */
    public function testConstructor() {
        $form1 = new KForm('examples/basic');
        $form2 = new KForm(array(
            'fields' => array()
        ));
        $form3 = new KForm('does not exist');
    }

    public function testBasicInput() {
        $form = new KForm(array(
            'fields' => array(
                'basic' => array(
                    
                )
            )
        ));

        $this->assertTrue($form->fields['basic'] instanceof KForm_Input);
    }

    /**
     *
     * @dataProvider providerExplicitInput
     */
    public function testExplicitInput($field_type, $input_class) {
        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => $field_type
                )
            )
        ));
        $this->assertTrue($form->fields['name'] instanceof $input_class);
    }

    public function testInputCheckbox() {
        $checkbox = new KForm_Input_Checkbox('', array());
        $checkbox->set_val('on');

        $this->assertTrue($checkbox->get_val());

        $checkbox->set_val(null);

        $this->assertFalse($checkbox->get_val());
    }

    /**
     *
     * @dataProvider providerDataSource
     */
    public function testDataSourceLoading($data_source) {
        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => 'list',
                    'data_source' => $data_source
                )
            )
        ));
        
        foreach ( $this->mockDataSource() as $row) {
            $this->assertEquals($form->model['fields']['name']->model['items'][$row['id']], $row['text']);
        }
    }

    public function providerDataSource() {
        return array(
            array(
                array(
                    'callback' => array($this, 'mockDataSource'),
                    'val_field' => 'id',
                    'text_field' => 'text'
                )
            )
        );
    }

    public function mockDataSource() {
        return array(
            array('id' => 1, 'text' => 'txt1'),
            array('id' => 2, 'text' => 'txt2')
        );
    }

    public function providerExplicitInput() {
        return array(
            array('text', 'KForm_Input'),
            array('hidden', 'KForm_Input'),
            array('checkbox', 'KForm_Input_Checkbox'),
            array('password', 'KForm_Input'),
            array('list', 'KForm_Input_List'),
            array('submit', 'KForm_Input'),
            array('textarea', 'KForm_Input')
        );
    }

}