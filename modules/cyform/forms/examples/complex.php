<?php


return CyForm::model()->theme('gray')
        ->title('Complex CyForm example')
        ->field(CyForm::field('text')
            ->label('hello')
            ->descr('some useful description')
        )->field(CyForm::field('list')
            ->label('my list')
            ->multiple(TRUE)
            ->validator('not_empty')
            ->items(array(
                'f' => 'female',
                'm' => 'male'
            ))->source(CyForm::source(function(){
                    return JORK::from('Model_User')->exec('jork_test');
                })
                ->val('id')->text('name')
           )
        );

return array(
    'theme' => 'cyform/gray',
    'title' => 'complex form example',
    'attributes' => array(
        'action' => 'formtest/ajaxsave'
    ),
    'fields' => array(
        'name' => array(
            'type' => 'text',
            'label' => 'username',
            //'description' => 'required'
        ),
        'password' => array(
            'type' => 'password',
            'label' => 'password'
        ),
        'role' => array(
            'type' => 'list',
            'label' => 'role',
            'view' => 'select',
            'items' => array(
                '0' => 'user',
                '1' => 'admin'
            )
        ),
        'enabled' => array(
            'type' => 'checkbox',
            'label' => 'enabled'
        ),
        'about' => array(
            'type' => 'textarea',
            'label' => 'about'
        ),
        'gender' => array(
            'type' => 'list',
            'label' => 'gender',
            'view' => 'buttons',
            'items' => array(
                'f' => 'female',
                'm' => 'male'
            )
        ),
        'groups' => array(
            'type' => 'list',
            'label' => 'groups',
            'multiple' => true,
            'view' => 'buttons',
            'items' => array(
                '1' => 'group 01',
                '2' => 'group 02',
                '3' => 'group 03'
            )
        ),
        'expires' => array(
            'type' => 'date',
            'min_date' => array('year' => '2010', 'month' => '01', 'day' => '01'),
            'max_date' => 'now',
            'label' => 'expires'
        ),
        array(
            'type' => 'submit',
            'label' => 'ok'
        )
    )
);