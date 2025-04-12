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
 * @package     qtype_moopt
 * @category    admin
 * @copyright   2019 ZLB-ELC Hochschule Hannover <elc@hs-hannover.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$ADMIN->add('qtypesettings', new admin_category('qtypemooptfolder',
                new lang_string('pluginname', 'qtype_moopt')));

$settings = new admin_settingpage($section, get_string('commonsettings', 'qtype_moopt'), 'moodle/site:config');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext("qtype_moopt/lms_id", new lang_string('lmsid',
        'qtype_moopt'), "", '', PARAM_TEXT));
    $settings->add(new admin_setting_configpasswordunmask('qtype_moopt/lms_password',
        new lang_string('lmspassword','qtype_moopt'), '', ''));

    $communicators = [];
    foreach (qtype_moopt\utility\communicator\communicator_factory::$implementations as $c) {
        $communicators[$c] = $c;
    }
    $settings->add(new admin_setting_configselect("qtype_moopt/communicator",
        new lang_string('chose_communicator', 'qtype_moopt'), "",
        qtype_moopt\utility\communicator\communicator_factory::$implementations[0], $communicators));

    $serviceurlsetting = new admin_setting_configtext("qtype_moopt/service_url", new lang_string('service_url',
        'qtype_moopt'), "", '', PARAM_URL);
    $serviceurlsetting->set_updatedcallback(function() use ($serviceurlsetting) {
        // remove trailing slashes as they will interfere with url concatenation
        $url = $serviceurlsetting->get_setting();
        if(!is_null($url))
            $serviceurlsetting->write_setting(rtrim($url, '/'));
    });
    $settings->add($serviceurlsetting);
    $settings->add(new admin_setting_configduration("qtype_moopt/service_timeout",
                    new lang_string('timeout', 'qtype_moopt'), "", 10, 1));
    $settings->add(new admin_setting_configduration("qtype_moopt/service_client_polling_interval",
                    new lang_string('client_polling_interval', 'qtype_moopt'), "", 5, 1));
    $settings->add(new admin_setting_configtext("qtype_moopt/max_number_free_text_inputs",
                    new lang_string('ftsmaxnumfields', 'qtype_moopt'), "", 10, PARAM_INT));


}

$ADMIN->add('qtypemooptfolder', $settings);
// Tell core we already added the settings structure.
$settings = null;
