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
use qtype_moopt\exceptions\service_unavailable;

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

        if($httpstatuscode == 200)
            return json_decode($gradersjson, true);
        else if($httpstatuscode == 404)
            throw new resource_not_found_exception("Resource $url was not found.");
        throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL GET $url.\n\nServer reply:\n"
            .strip_tags($gradersjson));
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
        }
        throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL HEAD $url");
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
     * @param string $gradername
     * @param string $graderversion
     * @param string $gradeprocessid
     * @return \stdClass a stdClass with two fields:
     * 'finished' - a boolean value that indicates whether the gradeprocess finished or not,
     * 'response' - contains the response that came back from grappa
     */
    public function get_grading_result(string $gradername, string $graderversion, string $gradeprocessid) {
        $url = "{$this->serviceurl}/{$this->lmsid}/gradeprocesses/$gradeprocessid";
        list($response, $httpstatuscode) = $this->get_from_grappa($url);
        $ret = new \stdClass();
        if ($httpstatuscode == 202) {
            $ret->finished = false;
            $ret->response = json_decode($response)->estimatedSecondsRemaining;
        } else if ($httpstatuscode == 200) {
            $ret->finished = true;
            $ret->response = $response;
        } else if($httpstatuscode == 404) {
            throw new resource_not_found_exception("A grading result does not exist for grade process id $gradeprocessid");
        } else {
            throw new service_communicator_exception("Received HTTP status code $httpstatuscode when accessing URL GET $url");
        }
        return $ret;
    }

    /*
     * Utility functions to access grappa from here on
     */

    public function head_from_grappa($url, $options = array()): array {
        return $this->request_from_grappa($url, function($curl, $options) use ($url) {
            return $curl->head($url, $options);
        });
    }

    public function get_from_grappa($url, $params = array(), $options = array()): array {
        return $this->request_from_grappa($url, function($curl, $options) use ($url, $params) {
            return $curl->get($url, $params, $options);
        });
    }

    public function post_to_grappa($url, $content = '', $options = array()): array {
        return $this->request_from_grappa($url, function($curl, $options) use ($url, $content) {
            $curl->setHeader('Content-Type: application/octet-stream');
            return $curl->post($url, $content, $options);
        });
    }

    private function request_from_grappa($url, $curlfunc): array {
        $curl = new \curl();
        if (!isset($options['CURLOPT_TIMEOUT']))
            $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
        $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;
        $response = $curlfunc($curl, $options);
        $info = $curl->get_info();
        $errno = $curl->get_errno();
        if ($errno != 0) {
            // Errno indicates errors on transport level therefore this is
            // almost certainly an error we do not want http errors need
            // to be handled by each calling function individually.
            throw new service_unavailable("Error accessing \"$url\" ({$curl->error} (Code {$curl->errno}))");
        }

        $httpcode = $curl->get_info()['http_code'];
        if($httpcode == 503) { // the service or one of its sub services is unavailable
            // for whatever reason, curl ignores the response message
            // from the web service (meaning the actual error message)
            // in favour of its own meaningless generic message, so setting the
            // exception message to the response message has no real meaning anymore,
            // but it may still serve some puprose ...
            throw new service_unavailable($response);
        }
        return array($response, $info['http_code']);
    }

    // private function head_from_grappa($url, $options = array()) {
    //     $curl = new \curl();
    //     if (!isset($options['CURLOPT_TIMEOUT'])) {
    //         $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
    //     }
    //     $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;

    //     $response = $curl->head($url, $options);

    //     $info = $curl->get_info();
    //     $errno = $curl->get_errno();
    //     if ($errno != 0) {
    //         // Errno indicates errors on transport level therefore this is almost certainly an error we do not want
    //         // http errors need to be handled by each calling function individually.
    //         throw new \invalid_response_exception("Error accessing HEAD $url;  CURL error code: $errno;  Error: {$curl->error}");
    //     }

    //     return array($response, $info['http_code']);
    // }

    // private function post_to_grappa($url, $contents = '', $options = array()) {
    //     $curl = new \curl();
    //     if (!isset($options['CURLOPT_TIMEOUT'])) {
    //         $options['CURLOPT_TIMEOUT'] = $this->servicetimeout;
    //     }
    //     $options['CURLOPT_USERPWD'] = $this->lmsid . ':' . $this->lmspw;
    //     $curl->setHeader('Content-Type: application/octet-stream'); // content-type for a zip-file

    //     $response = $curl->post($url, $contents, $options);

    //     $info = $curl->get_info();
    //     $errno = $curl->get_errno();
    //     if ($errno != 0) {
    //         // Errno indicates errors on transport level therefore this is almost certainly an error we do not want
    //         // http errors need to be handled by each calling function individually.
    //         throw new \invalid_response_exception("Error accessing POST $url;  CURL error code: $errno;  Error: {$curl->error}");
    //     }

    //     return array($response, $info['http_code']);
    // }

    /*
     *
     *
     * Singleton related code from here on
     *
     *
     */

    public function __construct() {
        $this->serviceurl = get_config("qtype_moopt", "service_url");
        if(!isset($this->serviceurl) || empty($this->serviceurl))
            throw new service_communicator_exception("The web service URL is not configured or malformed.");
        $this->servicetimeout = get_config("qtype_moopt", "service_timeout");
        $this->lmsid = get_config("qtype_moopt", "lms_id");
        $this->lmspw = get_config("qtype_moopt", "lms_password");
    }
}

