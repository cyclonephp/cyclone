<?php

namespace cyclone\config;

/**
 * @author Bence Eros <crystal@cyclonephp.org>
 * @package cyclone
 */
interface Reader {

    public function read($key);
}
