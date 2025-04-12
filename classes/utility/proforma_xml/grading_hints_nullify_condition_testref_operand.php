<?php

namespace qtype_moopt\utility\proforma_xml;

class grading_hints_nullify_condition_testref_operand
{
    private $ref;
    private $subref;

    public function __construct($ref, $subref) {
        $this->ref = $ref;
        $this->subref = $subref;
    }

    public function get_ref() {
        return $this->ref;
    }

    public function get_subref() {
        return $this->subref;
    }
}