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

        $this->assertTrue($form->fields['basic'] instanceof KForm_Field);
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
        $checkbox = new KForm_Field_Checkbox(new KForm(array(
            'fields' => array()
        )), '', array());
        $checkbox->set_data('on');

        $this->assertTrue($checkbox->get_data());

        $checkbox->set_data(null);

        $this->assertFalse($checkbox->get_data());
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

    public function testLoadInput() {
        $form = new KForm(array(
            'fields' => array(
                'name1' => array(),
                'name2' => array()
            )
        ));

        $form->set_input(array(
            'name1' => 'val1',
            'name2' => 'val2',
            'name3' => 'val3'
        ), false);
        $this->assertEquals(count($form->fields), 2);
        $this->assertEquals($form->fields['name1']->get_data(), 'val1');
    }

    public function testValidation() {
        $form = new KForm('examples/basic');
        $form->set_input(array('name' => 'hello'));
        $this->assertEquals($form->fields['name']->validation_errors,
                array(
                    'numeric' => 'hello: invalid number format',
                    0 => 'username hello is not unique',
                    1 => 'username hello is not unique'
                ));
    }

    public function testResult() {
        $form = new KForm(array(
            'fields' => array(
                'name1' => array(
                    'type' => 'text',
                ),
                'name2' => array(
                    'type' => 'checkbox'
                ),
                'name3' => array(
                    'type' => 'list',
                    'items' => array(
                        'val1' => 'text1',
                        'val2' => 'text2'
                    )
                ),
                array(
                    'type' => 'submit',
                    'value' => 'Ok'
                )
            )
        ));
        $form->set_input(array(
            'name1' => 'val1',
            'name2' => true,
            'name3' => 'val2'
        ));
        $this->assertEquals($form->get_data(), array(
            'name1' => 'val1',
            'name2' => true,
            'name3' => 'val2'
        ));
        $this->assertTrue($form->get_data('stdClass') instanceof stdClass);
    }

    public function testOnEmpty() {
        $form = new KForm(array(
            'fields' => array(
                'name1' => array(
                    'type' => 'text',
                    'on_empty' => null
                )
            )
        ));
        $form->set_input(array('name1' => ''));
        $data = $form->get_data();
        $this->assertNull($data['name1']);
    }

    /**
     *
     * @dataProvider providerFieldDate
     */
    public function testFieldDate($date_string, $input, $date_format) {
        $form = new KForm(array(
            'fields' => array(
                'mydate' => array(
                    'type' => 'date'
                )
            )
        ));
        $form->model['fields']['mydate']->value_format = $date_format;

        $form->set_input(array(
           'mydate_year' => $input['year'],
           'mydate_month' => $input['month'],
           'mydate_day' => $input['day']
        ));
        $data = $form->get_data();
        $this->assertEquals($data['mydate'], $date_string);

        $form = new KForm(array(
            'fields' => array(
                'mydate' => array(
                    'type' => 'date'
                )
            )
        ));
        $form->model['fields']['mydate']->value_format = $date_format;
        $form->set_data(array('mydate' => $date_string));
        $data = $form->get_data();
        $this->assertEquals($data['mydate'], $date_string);
    }

    public function testOnCreate() {
        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => 'text',
                    'on_create' => 'hide',
                )
            )
        ));

        $form->render();
        $this->assertFalse(array_key_exists('name', $form->fields));

        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => 'text',
                    'on_create' => 'disable',
                )
            )
        ));
        $form->render();
        $this->assertEquals('disabled', $form->fields['name']->model['attributes']['disabled']);
    }

    public function testOnEdit() {
        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => 'text',
                    'on_edit' => 'hide',
                )
            )
        ));

        $form->set_data(array('name' => 'username'));
        $form->render();
        $this->assertFalse(array_key_exists('name', $form->fields));

        $form = new KForm(array(
            'fields' => array(
                'name' => array(
                    'type' => 'text',
                    'on_edit' => 'disable',
                )
            )
        ));
        $form->set_data(array('name' => 'username'));
        $form->render();
        $this->assertEquals('disabled', $form->fields['name']->model['attributes']['disabled']);
    }


    /**
     *
     * @dataProvider providerEdit
     */
    public function testEdit(array $fields, array $before_data
            , $progress_id_required, $input, array $after_data) {
        $cfg = Kohana::config('kform');
        unset($_SESSION[$cfg['progress_key']]);
        $form_before_submit = new KForm(array('fields' => $fields));
        $form_before_submit->set_data($before_data);

        if ($progress_id_required) {
            $this->assertArrayHasKey($cfg['progress_key'], $form_before_submit->fields);
        } else {
            $form_fields = $form_before_submit->fields;
            foreach ($before_data as $k => $v) {
                $this->assertArrayHasKey($k, $form_fields);
                $this->assertEquals($form_fields[$k]->get_data(), $v);
            }
        }

        $form_after_submit = new KForm(array('fields' => $fields));
        if ($progress_id_required) {
            $input[$cfg['progress_key']] = $form_before_submit->fields[$cfg['progress_key']]->get_data();
        }
        $form_after_submit->set_input($input);
        $result = $form_after_submit->get_data();
        $this->assertEquals($result, $after_data);
    }

    public function providerEdit() {
        $rval = array();
        $fields = array(
            'name1' => array('type' => 'text'),
            'name2' => array('type' => 'text'),
        );
        $before_data = array('name1' => 'val1', 'name2' => 'val2');
        $progress_id_required = false;
        $input = array('name1' => 'val1_', 'name2' => 'val2_');
        $after_data = array('name1' => 'val1_', 'name2' => 'val2_');
        $rval []= array($fields, $before_data, $progress_id_required, $input, $after_data);


        $fields = array(
            'name1' => array('type' => 'text'),
            'name2' => array('type' => 'text'),
        );
        $before_data = array('name1' => 'val1', 'name2' => 'val2', 'name3' => 'val3');
        $progress_id_required = true;
        $input = array('name1' => 'val1_', 'name2' => 'val2_');
        $after_data = array('name1' => 'val1_', 'name2' => 'val2_', 'name3' => 'val3');
        $rval []= array($fields, $before_data, $progress_id_required, $input, $after_data);


        $fields = array(
            'name1' => array('type' => 'text'),
            'name2' => array('type' => 'text'),
        );
        $before_data = array('name1' => 'val1', 'name2' => 'val2', 'name3' => 'val3');
        $progress_id_required = true;
        $input = array('name1' => 'val1_');
        $after_data = array('name1' => 'val1_', 'name2' => 'val2', 'name3' => 'val3');
        $rval []= array($fields, $before_data, $progress_id_required, $input, $after_data);

        return $rval;
    }

    public function providerFieldDate() {
        return array(
            array('2010-09-17', array('year' => '2010', 'month' => '09', 'day' => '17'), 'year-month-day'),
            array('09/17/2010', array('year' => '2010', 'month' => '09', 'day' => '17'), 'month/day/year')
        );
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
            array('text', 'KForm_Field'),
            array('hidden', 'KForm_Field'),
            array('checkbox', 'KForm_Field_Checkbox'),
            array('password', 'KForm_Field'),
            array('list', 'KForm_Field_List'),
            array('submit', 'KForm_Field'),
            array('textarea', 'KForm_Field'),
            array('date', 'KForm_Field_Date')
        );
    }

    public static function custom_callback($username) {
        return false;
    }

}