<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

defined('MOODLE_INTERNAL') || die();

class proforma_submission_xml_creator extends proforma_xml_creator {

    public function createSubmissionXML(bool $includeTask, string $taskFilenameOrUUID, array $files, string $resultformat, string $resultstructure, $studentfeedbacklevel, $teacherfeedbacklevel): string {
        $this->initXMLWriterForDocument();

        $xml = $this->xmlWriter;

        $xml->startDocument('1.0', 'UTF-8');
            $xml->startElement('submission');
              $xml->writeAttribute('xmlns', proforma_TASK_XML_NAMESPACE);
              
                if($includeTask){
                    $xml->startElement('included-task-file');
                        $xml->writeElement('attached-zip-file', $taskFilenameOrUUID);
                    $xml->endElement();
                } else{
                    $xml->startElement('external-task');
                      $xml->writeAttribute('uuid', $taskFilenameOrUUID);
                    $xml->endElement();
                }
                
                $xml->startElement('files');
                    foreach($files as $file){
                        $xml->startElement('file');
                            $xml->writeAttribute('mimetype', $file->get_mimetype());
                            $xml->writeElement('attached-bin-file', $file->get_filename());
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

}
