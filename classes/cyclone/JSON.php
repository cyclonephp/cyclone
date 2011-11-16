<?php

namespace cyclone;

class JSON {

    public static function encode($val) {
        if (is_scalar($val)) {
            if (is_string($val)) {
                return '"' . addcslashes($val, '"') . '"';
            } elseif (is_bool($val)) {
                return $val ? 'true' : 'false';
            }
            return $val;
        }

        if (is_object($val)) {
            if (method_exists($val, 'jsonSerializable')) {
                $val = $val->jsonSerializable();
            }
            return self::encode_as_object($val);
        }
        $rval = '[';
        $expected_next_idx = 0;

        foreach ($val as $key => $item) {
            if ($key !== $expected_next_idx) {
                return self::encode_as_object($val);
            }
            if ($key > 0) {
                $rval .= ',';
            }
            $rval .= self::encode($item);
            $expected_next_idx++;
        }
        $rval .= ']';
        return $rval;
    }

    private static function encode_as_object($val) {
        $rval = '{';
        $first = TRUE;
        foreach ($val as $key => $item) {
            if ($first) {
                $first = FALSE;
            } else {
                $rval .= ',';
            }
            $rval .= '"' . $key . '":' . self::encode($item);
        }
        $rval .= '}';
        return $rval;
    }

    public static function decode($json) {
        
    }

    public static function pretty_print($val) {
        
    }
}