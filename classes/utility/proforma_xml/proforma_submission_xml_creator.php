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

namespace qtype_moopt\utility\proforma_xml;

defined('MOODLE_INTERNAL') || die();

class proforma_submission_xml_creator extends proforma_xml_creator {

    const MIME_TYPE_TEXT_PATTERN = "#text/.*#";

    public function create_submission_xml(bool $includetask, string $taskfilenameoruuid, array $files, string $resultformat,
            string $resultstructure, $studentfeedbacklevel, $teacherfeedbacklevel,
            $gradinghints, $tests, $gradinghintsnamespace, $maxscorelms): string {
        $this->init_xml_writer_for_document();

        $xml = $this->xmlwriter;

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('submission');
        $xml->writeAttribute('xmlns', PROFORMA_TASK_XML_NAMESPACES[0]);
        // TODO: Maybe add another namespace for grading hints if it differs from proforma_TASK_XML_NAMESPACES[0] because *in the
        // future* there *might* be incompatibilities.

        if ($includetask) {
            $xml->startElement('included-task-file');
            $xml->writeElement('attached-zip-file', $taskfilenameoruuid);
            $xml->endElement();
        } else {
            $xml->startElement('external-task');
            $xml->writeAttribute('uuid', $taskfilenameoruuid);
            $xml->endElement();
        }

        if ($resultstructure == PROFORMA_MERGED_FEEDBACK_TYPE) {
            $gradinghintshelper = new grading_hints_helper($gradinghints, $tests, $gradinghintsnamespace);
            if (!$gradinghintshelper->is_empty()) {
                $maxscoregradinghints = $gradinghintshelper->calculate_max_score();
                if (abs($maxscoregradinghints - $maxscorelms) > 1E-5) {
                    $gradinghintshelper->adjust_weights($maxscorelms / $maxscoregradinghints);
                    $this->write_dom_element($gradinghints);
                }
            }
        }

        $xml->startElement('files');
        foreach ($files as $filename => $file) {
            // Remove leading 'submission/' directory path from the file name, since all submission file pathes
            // are declared relative to the 'submission/' directory
            $submissiondir = 'submission/';
            $filenamerelativetosubmissiondir = substr($filename, 0, strlen($submissiondir)) == $submissiondir ?
                $filenamerelativetosubmissiondir = substr($filename, strlen($submissiondir)) : $filename;

            $isstoredfile = $file instanceof \stored_file;
            $xml->startElement('file');
            $xml->writeAttribute('mimetype', $isstoredfile ? $file->get_mimetype() : 'text/*');
            if (!$isstoredfile || preg_match($this::MIME_TYPE_TEXT_PATTERN, $file->get_mimetype())) {
                $xml->writeElement('attached-txt-file', $filenamerelativetosubmissiondir);
            } else {
                $xml->writeElement('attached-bin-file', $filenamerelativetosubmissiondir);
            }
            $xml->endElement();
        }
        $xml->endElement();

        $xml->startElement('result-spec');
        $xml->writeAttribute('format', $resultformat);
        $xml->writeAttribute('structure', $resultstructure);
        $xml->writeElement('student-feedback-level', $studentfeedbacklevel);
        $xml->writeElement('teacher-feedback-level', $teacherfeedbacklevel);
        $xml->endElement();

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function write_dom_element($elem) {
        if ($elem->nodeType == XML_TEXT_NODE) {
            // Only use this node if it contains not only whitespace.
            $text = preg_replace('/\s+/', '', $elem->nodeValue);
            if (strlen($text) != 0) {
                $this->xmlwriter->text($elem->nodeValue);
            }
            return;
        } else if ($elem->nodeType == XML_CDATA_SECTION_NODE) {
            // Only use this node if it contains not only whitespace.
            $text = preg_replace('/\s+/', '', $elem->textContent);
            if (strlen($text) != 0) {
                $this->xmlwriter->text($elem->textContent);
            }
            return;
        }

        $this->xmlwriter->startElement($elem->localName);
        foreach ($elem->attributes as $attrib) {
            $this->xmlwriter->writeAttribute($attrib->nodeName, $attrib->nodeValue);
        }
        if ($elem->nodeType == XML_ELEMENT_NODE || $elem->nodeType == XML_CDATA_SECTION_NODE) {
            foreach ($elem->childNodes as $child) {
                $this->write_dom_element($child);
            }
        } else {
            throw new \invalid_state_exception("Grading hints contain an unexpected node type");
        }
        $this->xmlwriter->endElement();
    }

}
