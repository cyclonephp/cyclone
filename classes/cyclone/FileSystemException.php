<?php
namespace cyclone;

/**
 * @author Bence Erős <crystal@cyclonephp.org>
 * @package cyclone;
 */
class FileSystemException extends Exception {

    const FILE_NOT_FOUND = 1;

    const FILE_NOT_READABLE = 2;

    const FILE_NOT_WRITABLE = 3;

}
