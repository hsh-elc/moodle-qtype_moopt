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

/**
 * factory method pattern for the communicators
 * currently there is only one grading middleware and therefore only one communicator,
 * so it may seem a bit unnecessary
 */
class communicator_factory {

    public static $implementations = ['grappa'];

    public static function get_instance(): communicator_interface {
        $communicator = get_config("qtype_moopt", "communicator");
        switch ($communicator) {
            case 'grappa':
                return grappa_communicator::get_instance();
            default:
                throw new \moodle_exception("Invalid communicator set. Communicator '$communicator' is unknown");
        }
    }

}
