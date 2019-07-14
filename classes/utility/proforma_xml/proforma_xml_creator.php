<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

defined('MOODLE_INTERNAL') || die();

class proforma_xml_creator {

    protected $xmlWriter;

    public function __construct(){
        $this->xmlWriter = new \XMLWriter();
    }

    protected function initXMLWriterForDocument() {
        $this->xmlWriter->openMemory();
        $this->xmlWriter->setIndent(1);
        $this->xmlWriter->setIndentString(' ');
    }

}
