<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility;

use qtype_programmingtask\exceptions\grappa_exception;

defined('MOODLE_INTERNAL') || die();

class grappa_communicator {

    private $grappa_url;
    private $grappa_timeout;

    public function getGraders(): array {
        $url = "{$this->grappa_url}/graders";
        list($graders_json, $http_status_code) = $this->GETfromGrappa($url);

        if ($http_status_code != 200) {
            throw new grappa_exception("Received HTTP status code $http_status_code when accessing URL GET $url");
        }

        return json_decode($graders_json, true);
    }

    public function isTaskCached($uuid): bool {
        $url = "{$this->grappa_url}/tasks/$uuid";
        list(, $http_status_code) = $this->HEADfromGrappa($url);

        if ($http_status_code == 200) {
            return true;
        } else if ($http_status_code == 404) {
            return false;
        } else {
            throw new grappa_exception("Received HTTP status code $http_status_code when accessing URL HEAD $url");
        }
    }

    //#####################################
    //utility functions to access grappa from here on
    //#####################################

    private function GETfromGrappa($url, $params = array(), $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->grappa_timeout;
        }

        /**
         *
         * TODO: AUTHENTICATION
         *
         */
        $response = $curl->get($url, $params, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            //errno indicates errors on transport level therefore this is almost certainly an error we do not want
            //http errors need to be handled by each calling function individually
            throw new \invalid_response_exception("Error accessing GET $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return array($response, $info['http_code']);
    }

    private function HEADfromGrappa($url, $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->grappa_timeout;
        }

        /**
         *
         * TODO: AUTHENTICATION
         *
         */
        $response = $curl->head($url, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            //errno indicates errors on transport level therefore this is almost certainly an error we do not want
            //http errors need to be handled by each calling function individually
            throw new \invalid_response_exception("Error accessing HEAD $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return array($response, $info['http_code']);
    }

    private function POSTtoGrappa($url, $params = array(), $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->grappa_timeout;
        }
        /**
         *
         * TODO: AUTHENTICATION
         *
         */
        $curl->post($url, $params, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            //errno indicates errors on transport level therefore this is almost certainly an error we do not want
            //http errors need to be handled by each calling function individually
            throw new \invalid_response_exception("Error accessing POST $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return $info['http_code'];
    }

    //#####################################
    //Singleton related code from here on
    //#####################################

    protected function __construct() {
        $this->grappa_url = get_config("qtype_programmingtask", "grappa_url");
        $this->grappa_timeout = get_config("qtype_programmingtask", "grappa_timeout");
    }

    protected static $instance = null;

    public static function getInstance(): grappa_communicator {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __clone() {
        
    }

}
