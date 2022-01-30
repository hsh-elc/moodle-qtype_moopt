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

defined('MOODLE_INTERNAL') || die();

class backup_qtype_moopt_plugin extends backup_qtype_plugin
{

    /**
     * Get the name of this question type.
     * @return string returns 'moopt'.
     */
    protected static function qtype_name()
    {
        return 'moopt';
    }

    /**
     * @return backup_plugin_element the qtype information to attach to question element.
     */
    protected function define_question_plugin_structure()
    {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', self::qtype_name());
        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        $mooptoptions = new backup_nested_element('mooptoptions', array('id'),
            array('internaldescription',
                'taskuuid',
                'showstudscorecalcscheme',
                'enablefilesubmissions',
                'enablefreetextsubmissions',
                'ftsautogeneratefilenames',
                'ftsmaxnumfields',
                'ftsnuminitialfields',
                'ftsstandardlang',
                'graderid',
                'resultspecformat',
                'resultspecstructure',
                'teacherfeedbacklevel',
                'studentfeedbacklevel'
            ));

        $mooptfreetexts = new backup_nested_element('mooptfreetexts');
        $mooptfreetext = new backup_nested_element('mooptfreetext', array('id'),
            array('filename',
                'filecontent',
                'ftslang',
                'initialdisplayrows',
                'inputindex',
                'presetfilename'
            ));

        $mooptfiles = new backup_nested_element('mooptfiles');
        $mooptfile = new backup_nested_element('mooptfile', array('id'),
            array('filename',
                'fileid',
                'filearea',
                'filepath',
                'usagebylms',
                'usedbygrader',
                'visibletostudents'
            ));

        // Build the tree.
        $pluginwrapper->add_child($mooptoptions);
        $pluginwrapper->add_child($mooptfreetexts);
        $mooptfreetexts->add_child($mooptfreetext);
        $pluginwrapper->add_child($mooptfiles);
        $mooptfiles->add_child($mooptfile);

        // Set source to populate the data.
        $mooptoptions->set_source_table('qtype_moopt_options', array('questionid' => backup::VAR_PARENTID));
        $mooptfreetext->set_source_table('qtype_moopt_freetexts', array('questionid' => backup::VAR_PARENTID));
        $mooptfile->set_source_table('qtype_moopt_files', array('questionid' => backup::VAR_PARENTID));

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas()
    {
        return array(
            'taskfile'     => 'question_created',
            'taskxmlfile' => 'question_created',
            'attachedtaskfiles' => 'question_created',
            'embeddedtaskfiles' => 'question_created',
            'submissionzip' => 'question_created',
            'responsefilesresponsefile' => 'question_attempt',
            'responsefiles' => 'question_attempt',
            'responsefilesembedded' => 'question_attempt'
        );
    }
}
