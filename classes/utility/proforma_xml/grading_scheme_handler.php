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
        $this->gradingscheme = new grading_hints_node('grading_scheme');
        if ($this->gradinghintsroot != null) {
            $this->fill_node_with_combine_node_infos($this->gradinghintsroot, $this->gradingscheme);
        }
        if ($this->gradinghintsroot != null && $this->has_gradinghintsroot_children()) {
            $this->build_scheme_from_children($this->gradinghintsroot, $this->gradingscheme);
            $maxscore = $this->calculate_maxscore_from_children($this->gradingscheme);
            $this->gradingscheme->set_max_score($maxscore);
        } else {
            $this->build_scheme_without_children($this->gradingscheme);
        }
    }

    /**
     * returns the gradingscheme, will build the grading scheme if it is not already build
     */
    public function get_grading_scheme(): grading_hints_node {
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
     * This function will create a grading_hints_node for every test in the task and will fill it with test infos.
     * @param \DOMElement $elem the root element of the grading hints
     * @param grading_hints_node $gradingscheme
     */
    private function build_scheme_without_children(grading_hints_node $gradingscheme) {
        foreach ($this->tests as $key => $value) {
            $test = new grading_hints_node($key);
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
     * This function will build a subtree of grading_hints_nodes that represents a part of the gradingscheme.
     * This function does not calculate the maximum score of the grading scheme.
     * @param \DOMElement $rootelem The root element of the xml subtree if the gradingscheme (grading hints)
     * @param grading_hints_node $rootnode The root node of the subtree
     */
    function build_scheme_from_children(\DOMElement $rootelem, grading_hints_node $rootnode) {
        $counter = 0;

        foreach ($rootelem->getElementsByTagNameNS($this->namespace, 'test-ref') as $testref) {
            $test = new grading_hints_node($rootnode->get_id() . '_' . $counter++);
            $rootnode->add_child($test);
            $test->set_type('test');
            $test->set_heading(get_string('test', 'qtype_moopt') . ' ');

            $refid = $testref->getAttribute('ref');
            $test->set_refid($refid);

            if ($testref->hasAttribute('sub-ref')) {
                $test->set_subref($testref->getAttribute('sub-ref'));
            }

            $this->fill_grading_hints_node_with_testref_infos($testref, $test);

            $this->fill_node_with_nullify_conditions($test, $testref);
        }
        foreach ($rootelem->getElementsByTagNameNS($this->namespace, 'combine-ref') as $combineref) {
            $combine = new grading_hints_node($rootnode->get_id() . '_' . $counter++);
            $rootnode->add_child($combine);
            $combine->set_type('combine');
            $combine->set_heading(get_string('combinedtest', 'qtype_moopt'));

            $refid = $combineref->getAttribute('ref');
            $combine->set_refid($refid);

            $this->fill_node_with_combine_node_infos($this->gradinghintscombines[$combine->get_refid()], $combine);

            $this->fill_node_with_nullify_conditions($combine, $combineref);

            $this->build_scheme_from_children($this->gradinghintscombines[$combine->get_refid()], $combine);
        }
    }
    /**
     * It will calculate the maximum score of a subtree of the gradingscheme.
     * @param grading_hints_node $root The root node of the subtree
     * @return int|mixed The maximum score of the subtree of the gradingscheme
     * @throws \coding_exception
     */
    private function calculate_maxscore_from_children(grading_hints_node $root)
    {
        switch ($root->get_accumulator_function()) {
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
            default:
                $maxvalue = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
        }

        foreach ($root->get_children() as $child) {
            $maxscore = 0;
            if ($child->get_type() == 'test') {
                $maxscore = $child->get_weight() * $this->scorecompensationfactor;
            } else if ($child->get_type() == 'combine') {
                $maxscore = $child->get_weight() * $this->calculate_maxscore_from_children($child);
            }
            $child->set_max_score($maxscore);
            $maxvalue = $mergefunc($maxvalue, $maxscore);
        }

        return $maxvalue;
    }

    /**
     * Will fill a grading_hints_node with all general information like: title, description, internal-description, accumulator function
     * @param \DOMElement $elem The xml element to get the information from
     * @param grading_hints_node $node The node to be filled with information
     */
    private function fill_node_with_combine_node_infos(\DOMElement $elem, grading_hints_node $node) {
        if (($list = $this->xpathtask->query('./p:title', $elem))->length == 1) {
            $node->set_title($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:description', $elem))->length == 1) {
            $node->set_description($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:internal-description', $elem))->length == 1) {
            $node->set_internal_description($list[0]->nodeValue);
        }
        $function = 'min';
        if ($elem->hasAttribute('function')) {
            $function = $elem->getAttribute('function');
        }
        $node->set_accumulator_function($function);
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $node->set_weight($weight);
    }

    /**
     * Fills a grading_hints_node, that represents a test, with test information
     * @param \DOMElement $elem The testref xml element
     * @param grading_hints_node $node The node that represents the test in the gradingscheme
     */
    private function fill_grading_hints_node_with_testref_infos(\DOMElement $elem, grading_hints_node $node)
    {
        $refid = $elem->getAttribute('ref');
        if (($titlelist = $elem->getElementsByTagNameNS($this->namespace, 'title'))->length == 1) {
            $node->set_title($titlelist[0]->nodeValue);
        } else {
            $node->set_title($this->tests[$refid] !== null && $this->tests[$refid]->getElementsByTagNameNS($this->namespace, 'title')[0]->nodeValue);
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
            $node->set_description($list[0]->nodeValue);
        } else {
            if ($this->tests[$refid] !== null && ($list = $this->tests[$refid]->getElementsByTagNameNS($this->namespace, 'description'))->length == 1) {
                $node->set_description($list[0]->nodeValue);
            }
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace, 'internal-description'))->length == 1) {
            $node->set_internal_description($list[0]->nodeValue);
        } else {
            if ($this->tests[$refid] !== null && ($list = $this->tests[$refid]->getElementsByTagNameNS(
                    $this->namespace, 'internal-description'))->length == 1) {
                $node->set_internal_description($list[0]->nodeValue);
            }
        }
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $node->set_weight($weight);
    }

    /**
     * Fills a node with all the nullifying data
     * @param grading_hints_node $node
     * @param \DOMElement $elem The DOMElement associated with $node
     */
    private function fill_node_with_nullify_conditions(grading_hints_node $node, \DOMElement $elem) {
        foreach ($elem->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            if ($child->localName == 'nullify-condition') {
                $node->set_nullifyconditionroot(new grading_hints_nullify_condition($child, $this->namespace, $this->scorecompensationfactor));
                break;
            } elseif ($child->localName == 'nullify-conditions') {
                $node->set_nullifyconditionroot(new grading_hints_nullify_conditions($child, $this->namespace, $this->scorecompensationfactor));
                break;
            }
        }
    }
}