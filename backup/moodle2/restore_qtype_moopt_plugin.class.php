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

class restore_qtype_moopt_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        $this->step->log('Restore called', backup::LOG_INFO);

        $paths = array();

        // List the relevant paths in the XML.
        $elements = array(
            'qtype_moopt_options' => '/mooptoptions',
            'qtype_moopt_files' => '/mooptfiles/mooptfile',
            'qtype_moopt_freetexts' => '/mooptfreetexts/mooptfreetext'
        );
        foreach ($elements as $elename => $path) {
            $paths[] = new restore_path_element($elename, $this->get_pathfor($path));
        }

        return $paths;
    }

    /**
     * Process MooPT options.
     * @param array/object $data the data from the backup file.
     */
    public function process_qtype_moopt_options($data) {
        global $DB;

        $data = (object)$data;

        // Detect if the question is created or mapped.
        $questioncreated = (bool) $this->get_mappingid('question_created', $this->get_old_parentid('question'));

        // If the question is being created by restore, save the options.
        if ($questioncreated) {
            $oldid = $data->id;
            $data->questionid = $this->get_new_parentid('question');
            $newid = $DB->insert_record('qtype_moopt_options', $data);
            // set this mapping because the links decoder will require it, see define_decode_contents()
            $this->set_mapping('qtype_moopt_options', $oldid, $newid);
        }
    }

    /**
     * Process MooPT files.
     * @param array/object $data the data from the backup file.
     */
    public function process_qtype_moopt_files($data) {
        global $DB;

        $data = (object)$data;
        // Detect if the question is created or mapped.
        $questioncreated = $this->get_mappingid('question_created', $this->get_old_parentid('question'));

        // If the question is being created, save this input.
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $DB->insert_record('qtype_moopt_files', $data, false);
        }
    }

    /**
     * Process MooPT freetext options.
     * @param array/object $data the data from the backup file.
     */
    public function process_qtype_moopt_freetexts($data) {
        global $DB;

        $data = (object)$data;

        // Detect if the question is created or mapped.
        $questioncreated = $this->get_mappingid('question_created', $this->get_old_parentid('question'));

        // If the question is being created, save this input.
        if ($questioncreated) {
            $oldid = $data->id;
            $data->questionid = $this->get_new_parentid('question');
            $newid = $DB->insert_record('qtype_moopt_freetexts', $data);
            // set this mapping because the links decoder will require it, see define_decode_contents()
            $this->set_mapping('qtype_moopt_freetexts', $oldid, $newid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {
        return array(
            new restore_decode_content('qtype_moopt_options', array('internaldescription'),
                'qtype_moopt_options'),
            new restore_decode_content('qtype_moopt_freetexts', array('filecontent'),
                'qtype_moopt_freetexts'),
        );
    }
}
