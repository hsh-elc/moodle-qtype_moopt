<?php

namespace qtype_programmingtask\exceptions;

defined('MOODLE_INTERNAL') || die();

/**
 * Exception indicating an error or an unexpected response
 *  returned from grappa
 */
class grappa_exception extends \moodle_exception {

    /**
     * Constructor
     * @param string $debuginfo some detailed information
     */
    function __construct($debuginfo = null) {
        parent::__construct('grappa', 'debug', '', null, $debuginfo);
    }

}
