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

namespace qtype_moopt\exceptions;

defined('MOODLE_INTERNAL') || die();

/**
 * Exception indicating connection problems on a network level,
 * e.g. curl trying to reach the grappa webservice in vain,
 * or grappa returning HTTP 503 Service Unavailable due to a
 * required subservice being unavailable, such as redis or docker.
 */
class service_unavailable extends \moodle_exception {
    public function __construct($debuginfo = null) {
        parent::__construct('serviceunavailable', 'qtype_moopt', '', $debuginfo, $debuginfo);
    }
}
