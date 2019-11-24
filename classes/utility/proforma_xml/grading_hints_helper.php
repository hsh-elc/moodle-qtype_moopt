<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

/**
 * Description of proforma_helper
 *
 * @author robin
 */
class grading_hints_helper {

    private $grading_hints;
    private $namespace;
    private $grading_hints_combines;

    public function __construct(\DOMElement $grading_hints, $namespace) {
        $this->grading_hints = $grading_hints;
        $this->namespace = $namespace;
        foreach ($this->grading_hints->getElementsByTagNameNS($namespace, "combine") as $combine) {
            $this->grading_hints_combines[$combine->getAttribute('id')] = $combine;
        }
    }

    public function calculate_max_score() {
        return $this->calculate_max_score_internal($this->grading_hints->getElementsByTagNameNS($this->namespace, "root")[0]);
    }

    private function calculate_max_score_internal(\DOMElement $elem) {
        $function = "min";
        if ($elem->hasAttribute('function')) {
            $function = $elem->getAttribute('function');
        }
        switch ($function) {
            case 'min':
                $value = PHP_INT_MAX;
                $merge_func = 'min';
                break;
            case 'max':
                $value = 0;
                $merge_func = 'max';
                break;
            case 'sum':
                $value = 0;
                $merge_func = function($a, $b) {
                    return $a + $b;
                };
                break;
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace, 'test-ref') as $testref) {

            $weight = 1;
            if ($testref->hasAttribute('weight')) {
                $weight = $testref->getAttribute('weight');
            }

            $value = $merge_func($value, $weight);
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace, 'combine-ref') as $combineref) {

            $refid = $combineref->getAttribute('ref');
            $combine = $this->grading_hints_combines[$refid];
            $maxScore = $this->calculate_max_score_internal($combine);
            $weight = 1;
            if ($combineref->hasAttribute('weight')) {
                $weight = $combineref->getAttribute('weight');
            }

            $value = $merge_func($value, $maxScore * $weight);
        }

        return $value;
    }

    public function adjust_weights($factor) {
        $this->adjust_weights_internal($this->grading_hints->getElementsByTagNameNS($this->namespace, "root")[0], $factor);
        foreach ($this->grading_hints->getElementsByTagNameNS($this->namespace, "combine") as $combine) {
            $this->adjust_weights_internal($combine, $factor);
        }
    }

    private function adjust_weights_internal(\DOMElement $elem, $factor) {
        foreach ($elem->getElementsByTagNameNS($this->namespace, 'test-ref') as $testref) {
            $weight = 1;
            if ($testref->hasAttribute('weight')) {
                $weight = $testref->getAttribute('weight');
            }
            $testref->setAttribute('weight', $weight * $factor);
        }
    }

    public function isEmpty(): bool {
        $root = $this->grading_hints->getElementsByTagNameNS($this->namespace, "root")[0];
        return $root->getElementsByTagNameNS($this->namespace, "test-ref")->length == 0 && $root->getElementsByTagNameNS($this->namespace, "combine-ref")->length == 0;
    }

}
