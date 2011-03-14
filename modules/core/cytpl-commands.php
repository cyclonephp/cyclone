<?php

function lng($params) {
            $p = $params[0];
            if ($p{0} != '$') {
                $p = "'" . $p . "'";
            }
            return '<?php echo __(' . $p . '); ?>';
        }

return array(
    'i' => array(
        'callback' => 'lng',
        'params' => array(0)
    )
);