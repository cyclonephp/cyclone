<?php

namespace cyclone\config;

/**
 * @author Bence Eros <crystal@cyclonephp.com>
 * @package cyclone
 */
interface Reader {

    public function read($key);
}
