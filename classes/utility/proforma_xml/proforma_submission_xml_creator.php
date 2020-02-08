<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

defined('MOODLE_INTERNAL') || die();

class proforma_submission_xml_creator extends proforma_xml_creator {

    const MIME_TYPE_TEXT_PATTERN = "#text/.*#";

    public function createSubmissionXML(bool $includeTask, string $taskFilenameOrUUID, array $files, string $resultformat, string $resultstructure, $studentfeedbacklevel, $teacherfeedbacklevel,
            $grading_hints, $grading_hints_namespace, $max_score_lms): string {
        $this->initXMLWriterForDocument();

        $xml = $this->xmlWriter;

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('submission');
        $xml->writeAttribute('xmlns', proforma_TASK_XML_NAMESPACES[0]);
        //TODO: Maybe add another namespace for grading hints if it differs from proforma_TASK_XML_NAMESPACES[0] because *in the future* there *might* be incompatibilities

        if ($includeTask) {
            $xml->startElement('included-task-file');
            $xml->writeElement('attached-zip-file', $taskFilenameOrUUID);
            $xml->endElement();
        } else {
            $xml->startElement('external-task');
            $xml->writeAttribute('uuid', $taskFilenameOrUUID);
            $xml->endElement();
        }

        if ($resultstructure == proforma_MERGED_FEEDBACK_TYPE) {
            $grading_hints_helper = new grading_hints_helper($grading_hints, $grading_hints_namespace);
            if (!$grading_hints_helper->isEmpty()) {
                $max_score_grading_hints = $grading_hints_helper->calculate_max_score();
                if (abs($max_score_grading_hints - $max_score_lms) > 1E-5) {
                    $grading_hints_helper->adjust_weights($max_score_lms / $max_score_grading_hints);
                    $this->writeDomElement($grading_hints);
                }
            }
        }

        $xml->startElement('files');
        foreach ($files as $filename => $file) {
            $isStoredFile = $file instanceof stored_file;
            $xml->startElement('file');
            $xml->writeAttribute('mimetype', $isStoredFile ? $file->get_mimetype() : 'text/*');
            if (!$isStoredFile || preg_match($this::MIME_TYPE_TEXT_PATTERN, $file->get_mimetype())) {
                $xml->writeElement('attached-txt-file', $filename);
            } else {
                $xml->writeElement('attached-bin-file', $filename);
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

    private function writeDomElement($elem) {
        if ($elem->nodeType == XML_TEXT_NODE) {
            //Only use this node if it contains not only whitespace
            $text = preg_replace('/\s+/', '', $elem->nodeValue);
            if (strlen($text) != 0) {
                $this->xmlWriter->text($elem->nodeValue);
            }
            return;
        }

        $this->xmlWriter->startElement($elem->localName);
        foreach ($elem->attributes as $attrib) {
            $this->xmlWriter->writeAttribute($attrib->nodeName, $attrib->nodeValue);
        }
        if ($elem->nodeType == XML_ELEMENT_NODE) {
            foreach ($elem->childNodes as $child) {
                $this->writeDomElement($child);
            }
        } else {
            throw new invalid_state_exception("Grading hints contain an unexpected node type");
        }
        $this->xmlWriter->endElement();
    }

}
