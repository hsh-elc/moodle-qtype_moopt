<?php

namespace qtype_moopt\utility\proforma_xml;

/**
 * Represents a composite nullifycondition
 */
class grading_hints_nullify_conditions
{
    private $title;
    private $description;
    private $internaldescription;
    private $composeoperator;
    /**
     * @var array A list of operands, the operands can be of type grading_hints_nullify_condition or of type grading_hints_nullify_conditions
     */
    private $operands;

    /**
     * Constructs the composite nullifycondition based on a DOMElement, that contains the composite nulifycondition data
     * @param \DOMElement $elem The nullify-conditions element from which the information will be retrieved
     * @param $namespace
     */
    public function __construct(\DOMElement $elem, $namespace) {
        $this->title = $elem->getElementsByTagNameNS($namespace, 'title')[0]->nodeValue;
        $this->description = $elem->getElementsByTagNameNS($namespace, 'description')[0]->nodeValue;
        $this->internaldescription = $elem->getElementsByTagNameNS($namespace, 'internal-description')[0]->nodeValue;

        $this->composeoperator = $elem->getAttribute('compose-op');

        $this->operands = array();

        foreach ($elem->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            if ($child->localName == 'nullify-condition') {
                $this->operands[] = new grading_hints_nullify_condition($child, $namespace);
            } elseif ($child->localName == 'nullify-conditions') {
                $this->operands[] = new grading_hints_nullify_conditions($child, $namespace);
            }
        }
    }

    public function get_title() {
        return $this->title;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_internaldescription() {
        return $this->internaldescription;
    }

    public function get_composeoperator() {
        return $this->composeoperator;
    }

    public function get_operands() {
        return $this->operands;
    }
}