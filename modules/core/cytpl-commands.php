<?php

return array(
    'i' => array(
        'callback' => function($params) {
            $p = $params[0];
            if ($p{0} != '$') {
                $p = "'" . $p . "'";
            }
            return '<?php echo __(' . $p . '); ?>';
        },
        'params' => array(0)
    )
);