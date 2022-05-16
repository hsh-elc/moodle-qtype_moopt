<?php

namespace qtype_moopt\utility\proforma_xml;

class grading_hints_nullify_condition
{
    private $title;
    private $description;
    private $internaldescription;
    private $compareoperator;
    private $leftoperand;
    private $rightoperand;

    /**
     * Constructs the nullifycondition based on a DOMElement, that contains the nullifycondition data
     * @param \DOMElement $elem The nullify-condition element from which the information will be retrieved
     * @param $namespace
     */
    public function __construct(\DOMElement $elem, $namespace) {
        if (($foundElems = $elem->getElementsByTagNameNS($namespace, 'title'))->length == 1) {
            $this->title = $foundElems[0]->nodeValue;
        }
        if (($foundElems = $elem->getElementsByTagNameNS($namespace, 'description'))->length == 1) {
            $this->description = $foundElems[0]->nodeValue;
        }
        if (($foundElems = $elem->getElementsByTagNameNS($namespace, 'internal-description'))->length == 1) {
            $this->internaldescription = $foundElems[0]->nodeValue;
        }

        $this->compareoperator = $elem->getAttribute('compare-op');

        $operands = [];
        foreach ($elem->childNodes as $childnode) {
            if ($childnode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($childnode->localName) {
                case 'nullify-combine-ref':
                    $operand = new grading_hints_nullify_condition_combineref_operand($childnode->getAttribute('ref'));
                    $operands[] = $operand;
                    break;
                case 'nullify-test-ref':
                    $operand = new grading_hints_nullify_condition_testref_operand($childnode->getAttribute('ref'), $childnode->getAttribute('sub-ref'));
                    $operands[] = $operand;
                    break;
                case 'nullify-literal':
                    $operand = floatval($childnode->getAttribute('value'));
                    $operands[] = $operand;
                    break;
            }
        }

        $this->leftoperand = $operands[0];
        $this->rightoperand = $operands[1];
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

    public function get_compareoperator() {
        return $this->compareoperator;
    }

    public function get_leftoperand() {
        return $this->leftoperand;
    }

    public function get_rightoperand() {
        return $this->rightoperand;
    }
}