<?php

namespace qtype_moopt\utility\proforma_xml;

class grading_scheme_handler {

    private $namespace;
    private $gradinghints;
    private $testselement;
    private $gradinghintscombines;
    private $gradinghintsroot;
    private $tests;
    private $maxscorelms;
    private $scorecompensationfactor;
    private $gradingscheme;
    private $xpathtask;

    public function __construct($gradinghints, $testselement, $namespace, $maxscorelms, $xpathtask) {
        $this->gradinghints = $gradinghints;
        $this->testselement = $testselement;
        $this->namespace = $namespace;
        $this->maxscorelms = $maxscorelms;
        $this->xpathtask = $xpathtask;
        $this->scorecompensationfactor = 1;

        $this->gradinghintscombines = [];
        $this->tests = [];

        $this->init();
    }

    /**
     * preprocesses the combines of the grading hints and all the tests, so they can be used later
     */
    private function init()
    {
        // Preprocess grading hints.
        if ($this->gradinghints != null) {
            $this->gradinghintsroot = $this->gradinghints->getElementsByTagNameNS($this->namespace, "root")[0];
            foreach ($this->gradinghints->getElementsByTagNameNS($this->namespace, "combine") as $combine) {
                $this->gradinghintscombines[$combine->getAttribute('id')] = $combine;
            }
        }
        // Preprocess tests.
        foreach ($this->testselement->getElementsByTagNameNS($this->namespace, "test") as $test) {
            $this->tests[$test->getAttribute('id')] = $test;
        }
        if ($this->gradinghintsroot != null && $this->has_children($this->gradinghintsroot)) {
            $gradinghintshelper = new grading_hints_helper($this->gradinghints, $this->testselement, $this->namespace);
            $maxscoregradinghints = $gradinghintshelper->calculate_max_score();
            if (abs($maxscoregradinghints - $this->maxscorelms) > 1e-5) {
                // - scorecompensationfactor is the scaling value for all student and max scores
                // - maxscorelms is the question's default mark
                $this->scorecompensationfactor = $this->maxscorelms / $maxscoregradinghints;
            }
        }
    }

    /**
     * builds the gradingscheme. The gradingscheme starts with a root node "gradingscheme"
     * which contains all other combined tests and tests as child nodes in a tree like manner.
     * If the gradinghints root element does not contain any testref and combinref elements,
     * the "gradingscheme" node will contain all tests as direct child nodes
     */
    public function build_grading_scheme() {
        $this->gradingscheme = new grading_hints_text_node('grading_scheme');
        if ($this->gradinghintsroot != null && $this->has_gradinghintsroot_children()) {
            $this->fill_node_with_combine_node_infos($this->gradinghintsroot, $this->gradingscheme);
            $maxscore = $this->calculate_maxscore_from_children($this->gradinghintsroot, $this->gradingscheme);
            $this->gradingscheme->set_max_score($maxscore);
        } else {
            $this->build_scheme_without_children($this->gradinghintsroot, $this->gradingscheme);
        }
    }

    /**
     * returns the gradingscheme, will build the grading scheme if it is not already build
     */
    public function get_grading_scheme(): grading_hints_text_node {
        if ($this->gradingscheme === null) {
            $this->build_grading_scheme();
        }
        return $this->gradingscheme;
    }

    public function get_scorecompensationfactor() {
        return $this->scorecompensationfactor;
    }

    public function has_gradinghintsroot_children() {
        return $this->has_children($this->gradinghintsroot);
    }

    public function has_children(\DOMElement $elem) {
        return $elem->getElementsByTagNameNS($this->namespace, 'test-ref')->length +
            $elem->getElementsByTagNameNS($this->namespace, 'combine-ref')->length != 0;
    }

    /**
     * Builds the grading scheme of the task when there are no childs in the grading hints root element.
     * This function will create a grading_hints_text_node for every test in the task and will fill it with test infos.
     * @param \DOMElement $elem the root element of the grading hints
     * @param grading_hints_text_node $gradingscheme
     */
    private function build_scheme_without_children(?\DOMElement $elem, grading_hints_text_node $gradingscheme) {

        $function = 'min';

        if ($elem != null) {
            if (($list = $elem->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
                $gradingscheme->set_description($list[0]->nodeValue);
            }
            if (($list = $elem->getElementsByTagNameNS($this->namespace, 'internal-description'))->length == 1) {
                $gradingscheme->set_internal_description($list[0]->nodeValue);
            }

            if ($elem->hasAttribute('function')) {
                $function = $elem->getAttribute('function');
            }
        }

        $gradingscheme->set_accumulator_function($function);

        foreach ($this->tests as $key => $value) {

            $test = new grading_hints_text_node($key);
            $gradingscheme->add_child($test);

            // Get infos from tests.
            $test->set_title($this->tests[$key]->getElementsByTagNameNS($this->namespace, 'title')[0]->nodeValue);
            if (($list = $this->tests[$key]->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
                $test->set_description($list[0]->nodeValue);
            }
            if (($list = $this->tests[$key]->getElementsByTagNameNS(
                    $this->namespace, 'internal-description'))->length == 1) {
                $test->set_internal_description($list[0]->nodeValue);
            }
        }
    }

    /**
     * This function will build a subtree of grading_hints_text_nodes that represents a part of the gradingscheme.
     * It will calculate the maximum score of the subtree of the gradingscheme.
     * @param \DOMElement $elem The root element of the xml subtree of the gradingscheme (grading hints)
     * @param grading_hints_text_node $gradingscheme The root node of the subtree
     * @param bool $scalescoretolms
     * @return int|mixed The maximum score of the subtree of the gradingscheme
     * @throws \coding_exception
     */
    private function calculate_maxscore_from_children(\DOMElement $elem, grading_hints_text_node $gradingscheme,
                                                                  $scalescoretolms = true)
    {
        $function = "min";
        if ($elem->hasAttribute('function')) {
            $function = $elem->getAttribute('function');
        }
        $gradingscheme->set_accumulator_function($function);

        switch ($gradingscheme->get_accumulator_function()) {
            case 'min':
                $maxvalue = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
            case 'max':
                $maxvalue = 0;
                $mergefunc = 'max';
                break;
            case 'sum':
                $maxvalue = 0;
                $mergefunc = function ($a, $b) {
                    return $a + $b;
                };
                break;
        }

        $counter = 0;

        foreach ($elem->getElementsByTagNameNS($this->namespace, 'test-ref') as $testref) {
            $test = new grading_hints_text_node($gradingscheme->get_id() . '_' . $counter++);
            $test->set_type('test');

            $test->set_heading(get_string('test', 'qtype_moopt') . ' ');

            $refid = $testref->getAttribute('ref');
            $test->set_refid($refid);

            if ($testref->hasAttribute('sub-ref')) {
                $test->set_subref($testref->getAttribute('sub-ref'));
            }

            $this->fill_grading_hints_node_with_test_infos($testref, $test);

            $gradingscheme->add_child($test);

            $maxscore = $this->get_weighted_maxscore_testref($testref, $test, $scalescoretolms);
            $test->set_max_score($maxscore);

            $this->fill_node_with_nullify_conditions($test, $testref);

            $maxvalue = $mergefunc($maxvalue, $maxscore);
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace, 'combine-ref') as $combineref) {
            $combine = new grading_hints_text_node($gradingscheme->get_id() . '_' . $counter++);
            $combine->set_type('combine');

            $combine->set_heading(get_string('combinedtest', 'qtype_moopt'));

            $refid = $combineref->getAttribute('ref');
            $combine->set_refid($refid);

            $gradingscheme->add_child($combine);

            $this->fill_node_with_combine_node_infos($this->gradinghintscombines[$combine->get_refid()], $combine);

            $maxscore = $this->get_weighted_maxscore_combineref($combineref, $combine, $scalescoretolms);
            $combine->set_max_score($maxscore);

            $this->fill_node_with_nullify_conditions($combine, $combineref);

            $maxvalue = $mergefunc($maxvalue, $maxscore);
        }

        return $maxvalue;
    }

    /**
     * Will fill a grading_hints_text_node with the following information: title, description, internal-description
     * @param \DOMElement $elem The xml element to get the information from
     * @param grading_hints_text_node $node The node to be filled with information
     */
    private function fill_node_with_combine_node_infos(\DOMElement $elem, grading_hints_text_node $node) {
        if (($list = $this->xpathtask->query('./p:title', $elem))->length == 1) {
            $node->set_title($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:description', $elem))->length == 1) {
            $node->set_description($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:internal-description', $elem))->length == 1) {
            $node->set_internal_description($list[0]->nodeValue);
        }
    }

    /**
     * Calculates the weighted maximum score for a test
     * @param \DOMElement $elem The corresponding xml test-ref element to $node
     * @param grading_hints_text_node $node The node that represents a test
     * @param bool $scalescoretolms
     * @return float|int|string The maximum score for the test
     */
    private function get_weighted_maxscore_testref(\DOMElement $elem, grading_hints_text_node $node, $scalescoretolms = true) {
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $node->set_weight($weight);

        if ($scalescoretolms) {
            return $weight * $this->scorecompensationfactor;
        } else {
            return $weight;
        }
    }

    /**
     * Calculates the weighted maximum score for a combined test and will also process the childs of the combined test
     * @param \DOMElement $elem The corresponding xml combine-ref element to $node
     * @param grading_hints_text_node $node The node that represents a combined test
     * @param bool $scalescoretolms
     * @return float|int The maximum score for the combined test
     * @throws \coding_exception
     */
    private function get_weighted_maxscore_combineref(\DOMElement $elem, grading_hints_text_node $node, $scalescoretolms = true) {
        $combine = $this->gradinghintscombines[$node->get_refid()];
        $maxscore = $this->calculate_maxscore_from_children($combine, $node, $scalescoretolms);
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $node->set_weight($weight);
        return $maxscore * $weight;
    }

    /**
     * Fills a grading_hints_text_node, that represents a test, with test information
     * @param \DOMElement $elem The test xml element
     * @param grading_hints_text_node $node The node that represents the test in the gradingscheme
     */
    private function fill_grading_hints_node_with_test_infos(\DOMElement $elem, grading_hints_text_node $node)
    {
        $refid = $elem->getAttribute('ref');
        if (($titlelist = $elem->getElementsByTagNameNS($this->namespace, 'title'))->length == 1) {
            $node->set_title($titlelist[0]->nodeValue);
        } else {
            $node->set_title($this->tests[$refid]->getElementsByTagNameNS($this->namespace, 'title')[0]->nodeValue);
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
            $node->set_description($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
                $node->set_description($list[0]->nodeValue);
            }
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace, 'internal-description'))->length == 1) {
            $node->set_internal_description($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS(
                    $this->namespace, 'internal-description'))->length == 1) {
                $node->set_internal_description($list[0]->nodeValue);
            }
        }
    }

    /**
     * Fills a node with all the nullifying data
     * @param grading_hints_text_node $node
     * @param \DOMElement $elem The DOMElement associated with $node
     */
    private function fill_node_with_nullify_conditions(grading_hints_text_node $node, \DOMElement $elem) {
        foreach ($elem->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            if ($child->localName == 'nullify-condition') {
                $node->set_nullifyconditionroot(new grading_hints_nullify_condition($child, $this->namespace));
                break;
            } elseif ($child->localName == 'nullify-conditions') {
                $node->set_nullifyconditionroot(new grading_hints_nullify_conditions($child, $this->namespace));
                break;
            }
        }
    }
}