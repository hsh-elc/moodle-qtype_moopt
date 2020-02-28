<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\communicator;

class communicator_factory {

    public static $IMPLEMENTATIONS = ['grappa'];

    public static function getInstance(): communicator_interface {
        $communicator = get_config("qtype_programmingtask", "communicator");
        switch ($communicator) {
            case 'grappa':
                return grappa_communicator::getInstance();
            default:
                throw new \moodle_exception("Invalid communicator set. Communicator '$communicator' is unknown");
        }
    }

}
