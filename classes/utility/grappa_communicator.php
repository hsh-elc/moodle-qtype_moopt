<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility;

defined('MOODLE_INTERNAL') || die();

class grappa_communicator {

    /**
     *
     * TODO: AUTHORIZATION
     *
     */


    private $grappa_uri;

    public function getGraders(): array {
        $graders_json = file_get_contents("{$this->grappa_uri}/graders");
        return json_decode($graders_json, true);
    }

    //#####################################
    //Singleton related code from here on
    //#####################################

    protected function __construct() {
        //TODO: Read from DB
        $this->grappa_uri = "http://localhost/dummyserver";
    }

    protected static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __clone() {

    }

}
