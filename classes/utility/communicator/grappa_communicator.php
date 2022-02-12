<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace qtype_moopt\utility\communicator;

defined('MOODLE_INTERNAL') || die();

use qtype_moopt\exceptions\resource_not_found_exception;
use qtype_moopt\exceptions\service_communicator_exception;

require_once($CFG->libdir . '/filelib.php');

/**
 * class to communicate with grappa
 */
class grappa_communicator implements communicator_interface {

    private $serviceurl;
    private $servicetimeout;
    private $lmsid;
    private $lmspw;

    /**
     * @throws \invalid_response_exception
     * @throws grappa_exception
     */
    public function get_graders(): array {
        $url = "{$this->serviceurl}/graders";
        list($gradersjson, $httpstatuscode) = $this->get_from_grappa($url);

        if ($httpstatuscode != 200) {
            throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL GET $url. Returned contents: '$gradersjson'");
        }
        return json_decode($gradersjson, true);
    }

    /**
     * @throws \invalid_response_exception
     * @throws grappa_exception
     */
    public function is_task_cached($uuid): bool {
        $url = "{$this->serviceurl}/tasks/$uuid";
        list(, $httpstatuscode) = $this->head_from_grappa($url);

        if ($httpstatuscode == 200) {
            return true;
        } else if ($httpstatuscode == 404) {
            return false;
        } else {
            throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL HEAD $url");
        }
    }

    /**
     * @throws \invalid_response_exception
     * @throws grappa_exception
     */
    public function enqueue_submission(string $gradername, string $graderversion, bool $asynch, \stored_file $submissionfile) {
        $url = "{$this->serviceurl}/{$this->lmsid}/gradeprocesses?graderName=$gradername&graderVersion=$graderversion&async=$asynch";

        list($responsejson, $httpstatuscode) = $this->post_to_grappa($url, $submissionfile->get_content());
        if ($httpstatuscode != 201 /* = CREATED */) {
            error_log($responsejson);
            throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL POST $url. Returned contents: '$responsejson'");
        }
        return json_decode($responsejson)->gradeProcessId;
    }

    /**
     * @throws grappa_exception
     * @throws \invalid_response_exception
     */
    public function get_grading_result(string $gradername, string $graderversion, string $gradeprocessid) {
        $url = "{$this->serviceurl}/{$this->lmsid}/gradeprocesses/$gradeprocessid";
        list($response, $httpstatuscode) = $this->get_from_grappa($url);
        if ($httpstatuscode == 202) {
            return false;
        } else if ($httpstatuscode == 200) {
            return $response;
        } else if($httpstatuscode == 404) {
            throw new resource_not_found_exception("A grading result does not exist for grade process id $gradeprocessid");
        } else {
            throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL GET $url");
        }
    }

    /*
     *
     *
     * Utility functions to access grappa from here on
     *
     *
     *
     */

    private function get_from_grappa($url, $params = array(), $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
        }
        $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;

        $response = $curl->get($url, $params, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            // Errno indicates errors on transport level therefore this is almost certainly an error we do not want
            // http errors need to be handled by each calling function individually.
            throw new \invalid_response_exception("Error accessing GET $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return array($response, $info['http_code']);
    }

    private function head_from_grappa($url, $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
        }
        $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;

        $response = $curl->head($url, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            // Errno indicates errors on transport level therefore this is almost certainly an error we do not want
            // http errors need to be handled by each calling function individually.
            throw new \invalid_response_exception("Error accessing HEAD $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return array($response, $info['http_code']);
    }

    private function post_to_grappa($url, $contents = '', $options = array()) {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT'])) {
            $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
        }
        $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;
        $curl->setHeader('Content-Type: application/octet-stream'); // content-type for a zip-file

        $response = $curl->post($url, $contents, $options);

        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            // Errno indicates errors on transport level therefore this is almost certainly an error we do not want
            // http errors need to be handled by each calling function individually.
            throw new \invalid_response_exception("Error accessing POST $url;  CURL error code: $errno;  Error: {$curl->error}");
        }

        return array($response, $info['http_code']);
    }

    /*
     *
     *
     * Singleton related code from here on
     *
     *
     */

    protected function __construct() {
        $this->serviceurl = get_config("qtype_moopt", "service_url");
        $this->servicetimeout = get_config("qtype_moopt", "service_timeout");
        $this->lmsid = get_config("qtype_moopt", "lms_id");
        $this->lmspw = get_config("qtype_moopt", "lms_password");
    }

    protected static $instance = null;

    public static function get_instance(): grappa_communicator {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function __clone() {
        
    }

}
