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

/**
 * Plugin administration pages are defined here.
 *
 * @package     qtype_programmingtask
 * @category    admin
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$ADMIN->add('qtypesettings', new admin_category('qtypeprogrammingtaskfolder',
                new lang_string('pluginname', 'qtype_programmingtask')));

$settings = new admin_settingpage($section, get_string('commonsettings', 'qtype_programmingtask'), 'moodle/site:config');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext("qtype_programmingtask/grappa_url", new lang_string('grappa_url',
                            'qtype_programmingtask'), "", '', PARAM_URL));
    $settings->add(new admin_setting_configduration("qtype_programmingtask/grappa_timeout",
                    new lang_string('timeout', 'qtype_programmingtask'), "", 10, 1));
    $settings->add(new admin_setting_configduration("qtype_programmingtask/grappa_client_polling_interval",
                    new lang_string('client_polling_interval', 'qtype_programmingtask'), "", 5, 1));
    $settings->add(new admin_setting_configtext("qtype_programmingtask/max_number_free_text_inputs",
                    new lang_string('ftsmaxnumfields', 'qtype_programmingtask'), "", 1, PARAM_INT));

    $communicators = [];
    foreach (qtype_programmingtask\utility\communicator\communicator_factory::$implementations as $c) {
        $communicators[$c] = $c;
    }
    $settings->add(new admin_setting_configselect("qtype_programmingtask/communicator",
                    new lang_string('chose_communicator', 'qtype_programmingtask'), "",
                    qtype_programmingtask\utility\communicator\communicator_factory::$implementations[0], $communicators));
}

$ADMIN->add('qtypeprogrammingtaskfolder', $settings);
// Tell core we already added the settings structure.
$settings = null;

$ADMIN->add('qtypeprogrammingtaskfolder', new admin_externalpage('qtypeprogrammingtaskgradersettings',
                get_string('gradersettings', 'qtype_programmingtask'), $CFG->wwwroot .
                '/question/type/programmingtask/gradersettings.php'));
