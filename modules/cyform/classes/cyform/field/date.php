<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Date extends CyForm_Field {

    public $value_format = 'year-month-day';

    protected $suffixes = array(
        'year' => '_year',
        'month' => '_month',
        'day' => '_day'
    );

    public $value = array(
        'year' => null,
        'month' => null,
        'day' => null
    );


    public function  __construct(CyForm $form, $name, CyForm_Model_Field $model, $cfg) {
        parent::__construct($form, $name, $model, 'date', $cfg);
    }

    public function  pick_input(&$src, &$saved_data = array()) {
        $this->value = array(
            'year' => $src[$this->get_segment_name('year')],
            'month' => $src[$this->get_segment_name('month')],
            'day' => $src[$this->get_segment_name('day')]
        );
    }

    protected function get_segment_name($segment) {
        return $this->_model->name.$this->suffixes[$segment];
    }

    public function  set_data($val) {
        $escaped_value_format = str_replace('/', '\/', $this->value_format);
        $pattern = '/'.$escaped_value_format.'/';
        foreach (array_keys($this->value) as $segment) {
            $pattern = str_replace($segment, '(?P<'.$segment.'>\d+)', $pattern);
        }
        preg_match($pattern, $val, $matches);
        if (empty($matches))
            throw new Exception('invalid date format');

        $this->value = array(
            'year' => $matches['year'],
            'month' => $matches['month'],
            'day' => $matches['day']
        );
        // TODO validate
    }

    public function  get_data() {
        return strtr($this->value_format, $this->value);
    }

    protected function  before_rendering() {
        $this->_model->errors = $this->validation_errors;
        
        if (NULL === $this->_model->view) {
            $this->_model->view = 'date';
        }

        $min_date = $this->extract_date_definition('min_date');
        $max_date = $this->extract_date_definition('max_date');

        $year_seg = array(
            'value' => $this->value['year'],
            'name' => $this->get_segment_name('year'),
            'items' => array()
        );

        for ($y = $min_date['year']; $y <= $max_date['year']; ++$y) {
            if (strlen($y) < 2) {
                $year_seg['items'][$tmp = '0'.$y] = $tmp;
            } else {
                $year_seg['items'][$y] = $y;
            }
        }
        $this->_model->segments = array($year_seg);

        $month_seg = array(
            'value' => $this->value['month'],
            'name' => $this->get_segment_name('month'),
            'items' => array()
        );

        for ($m = 1; $m <= 12; ++$m) {
            if (strlen($m) < 2) {
                $month_seg['items'][$tmp = '0'.$m] = $tmp;
            } else {
                $month_seg['items'][$m] = $m;
            }
        }
        $this->_model->segments []= $month_seg;



        $day_seg = array(
            'value' => $this->value['day'],
            'name' => $this->get_segment_name('day'),
            'items' => array()
        );

        for ($d = 1; $d <= 31; ++$d) {
            if (strlen($d) < 2) {
                $day_seg['items'][$tmp = '0'.$d] = $tmp;
            } else {
                $day_seg['items'][$d] = $d;
            }
        }
        $this->_model->segments []= $day_seg;
    }

    protected function extract_date_definition($key) {
        if ('now' === $this->_model->$key) {
            return array(
                'year' => date('Y'),
                'month' => date('m'),
                'day' => date('d')
            );
        }
        return $this->_model->$key;
    }

}
