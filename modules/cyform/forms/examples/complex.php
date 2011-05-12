<?php


return CyForm::model()->theme('cyform/daffodil')
        ->title('Complex CyForm example')
        //->action('formtest/ajaxsave')
        ->field(CyForm::field('name')
            ->label('username'))

        ->field(CyForm::field('password', 'password')
            ->label('password')
        )->field(CyForm::field('role', 'list')
            ->label('role')
            ->view('select')
            ->items(array(
                '0' => 'user',
                '1' => 'admin'
            ))
        )->field(CyForm::field('enabled', 'checkbox')
             ->label('enabled')
        )->field(CyForm::field('about', 'textarea')
                ->label('about')
        )->field(CyForm::field('gender', 'list')
            ->label('gender')
            ->view('buttons')
            ->validator('not_empty')
            ->items(array(
                'f' => 'female',
                'm' => 'male'
            ))
        )->field(CyForm::field('groups', 'list')
                ->label('groups')
                ->multiple(TRUE)
                ->view('buttons')
                ->items(array(
                    '1' => 'group 01',
                    '2' => 'group 02',
                    '3' => 'group 03'
                ))
        )->field(CyForm::field('expires', 'date')
                ->label('expires')
                //->min_date('now')
                ->max_date(array('year' => '2015', 'month' => '05', 'day' => '22'))
        )->field(CyForm::field(NULL, 'submit')
                ->label('Ok'))


;

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