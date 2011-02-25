<?php

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package CyForm
 */
class CyForm_Field_Date extends KForm_Field {

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


    public function  __construct(KForm $form, $name, array $model) {
        parent::__construct($form, $name, $model, 'date');
    }

    public function  pick_input(&$src, &$saved_data = array()) {
        $this->value = array(
            'year' => $src[$this->get_segment_name('year')],
            'month' => $src[$this->get_segment_name('month')],
            'day' => $src[$this->get_segment_name('day')]
        );
    }

    protected function get_segment_name($segment) {
        return $this->name.$this->suffixes[$segment];
    }

    public function  set_data($val) {
        $escaped_value_format = str_replace('/', '\/', $this->value_format);
        $pattern = '/'.$escaped_value_format.'/';
        foreach (array_keys($this->value) as $segment) {
            $pattern = str_replace($segment, '(?P<'.$segment.'>\d+)', $pattern);
        }
        preg_match($pattern, $val, $matches);
        $this->value = array(
            'year' => $matches['year'],
            'month' => $matches['month'],
            'day' => $matches['day']
        );
    }

    public function  get_data() {
        return strtr($this->value_format, $this->value);
    }

    protected function  before_rendering() {
        $this->model['errors'] = $this->validation_errors;
        if ( ! array_key_exists('attributes', $this->model)) {
            $this->model['attributes'] = array();
        }
        
        if ( ! array_key_exists('view', $this->model)) {
            $this->model['view'] = 'date';
        }

        $this->model['segments'] = array();

        foreach (array_keys($this->value) as $segment) {
            $this->model['segments'] []= $this->build_segment_view_data($segment);
        }
    }

    protected function build_segment_view_data($segment) {
        static $min_date = null;
        static $max_date = null;
        if (null == $min_date && null == $max_date) {
            $min_date = $this->extract_date_definition('min_date', '1900-01-01');
            $max_date = $this->extract_date_definition('max_date');
        }
        $rval = array(
            'value' => $this->value[$segment],
            'name' => $this->get_segment_name($segment),
            'items' => array()
        );
        for ($i = $min_date[$segment]; $i <= $max_date[$segment]; $i++) {
            if (strlen($i) < 2) {
                $rval['items'][$tmp = '0'.$i] = $tmp;
            } else {
                $rval['items'][$i] = $i;
            }
        }
        return $rval;
    }

    protected function extract_date_definition($key, $default = 'now') {
        if ( ! array_key_exists($key, $this->model)) {
            $this->model[$key] = $default;
        }

        if ('now' === $this->model[$key]) {
            return array(
                'year' => date('Y'),
                'month' => date('m'),
                'day' => date('d')
            );
        }
        return $this->model[$key];
    }

}
