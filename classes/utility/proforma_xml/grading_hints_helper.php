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

/**
 * Description of proforma_helper
 *
 * @author robin
 */
class grading_hints_helper {

    private $gradinghints;
    private $testselement;
    private $namespace;
    private $gradinghintscombines;

    public function __construct($gradinghints, $testselement, $namespace) {
        $this->gradinghints = $gradinghints;
        $this->testselement = $testselement;
        $this->namespace = $namespace;
        if ($this->gradinghints != null) {
            foreach ($this->gradinghints->getElementsByTagNameNS($namespace, "combine") as $combine) {
                $this->gradinghintscombines[$combine->getAttribute('id')] = $combine;
            }
        }
    }

    public function calculate_max_score() {
        if ($this->gradinghints != null) {
            return $this->calculate_max_score_internal($this->gradinghints->getElementsByTagNameNS($this->namespace, "root")[0]);
        }
        return 1.0;
    }

    private function calculate_max_score_internal(\DOMElement $elem) {
        $function = "min";
        if ($elem->hasAttribute('function')) {
            $function = $elem->getAttribute('function');
        }
        switch ($function) {
            case 'min':
                $value = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
            case 'max':
                $value = 0;
                $mergefunc = 'max';
                break;
            case 'sum':
                $value = 0;
                $mergefunc = function($a, $b) {
                    return $a + $b;
                };
                break;
        }
        
        $testrefs = $elem->getElementsByTagNameNS($this->namespace, "test-ref");
        $combinerefs = $elem->getElementsByTagNameNS($this->namespace, "combine-ref");
        
        if ($testrefs->length == 0 && $combinerefs->length == 0) {
            // root node with no children by default accumulates all test results.
            if ($elem->localName == "root") {
				
				$counttests = $this->testselement->getElementsByTagNameNS($this->namespace, "test")->count();
				if ($counttests == 0) {
					// there should be tests, but if we don't have any, we assume a maximum score of 1
					$value= 1.0;
				} else {
					for ($i = 0; $i < $counttests; $i++) {
						$value = $mergefunc($value, 1.0);
					}
				}
            }

        } else {

            foreach ($testrefs as $testref) {

                $weight = 1;
                if ($testref->hasAttribute('weight')) {
                    $weight = $testref->getAttribute('weight');
                }

                $value = $mergefunc($value, $weight);
            }
            foreach ($combinerefs as $combineref) {

                $refid = $combineref->getAttribute('ref');
                $combine = $this->gradinghintscombines[$refid];
                $maxscore = $this->calculate_max_score_internal($combine);
                $weight = 1;
                if ($combineref->hasAttribute('weight')) {
                    $weight = $combineref->getAttribute('weight');
                }

                $value = $mergefunc($value, $maxscore * $weight);
            }
        }
        
        return $value;
    }

    public function adjust_weights($factor) {
        $this->adjust_weights_internal($this->gradinghints->getElementsByTagNameNS($this->namespace, "root")[0], $factor);
        foreach ($this->gradinghints->getElementsByTagNameNS($this->namespace, "combine") as $combine) {
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

    public function is_empty(): bool {
        if ($this->gradinghints == null) {
            return true;
        }
        $root = $this->gradinghints->getElementsByTagNameNS($this->namespace, "root")[0];
        return $root->getElementsByTagNameNS($this->namespace, "test-ref")->length == 0 &&
                $root->getElementsByTagNameNS($this->namespace, "combine-ref")->length == 0;
    }

}
