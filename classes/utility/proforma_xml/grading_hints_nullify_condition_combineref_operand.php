<?php

namespace qtype_moopt\utility\proforma_xml;

class grading_hints_nullify_condition_combineref_operand
{
    private $ref;

    public function __construct($ref) {
        $this->ref = $ref;
    }

    public function get_ref() {
        return $this->ref;
    }
}