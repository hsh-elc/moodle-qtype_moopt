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

use qtype_moopt\exceptions\service_communicator_exception;

defined('MOODLE_INTERNAL') || die();

class separate_feedback_handler {

    private $namespacegradinghints;
    private $namespacefeedback;
    private $gradinghints;
    private $testselement;
    private $separatetestfeedback;
    private $testresults;
    private $maxscorelms;
    private $scorecompensationfactor;
    private $calculatedscore;
    private $gradingscheme;
    private $detailedfeedback;
    private $summarisedfeedback;
    private $files;
    private $feedbackfiles;
    private $xpathtask;
    private $hasgradinghintsrootchildren;

    public function __construct($gradinghints, \DOMElement $tests, \DOMElement $separatetestfeedback, \DOMElement $feedbackfiles,
            $namespacegradinghints, $namespacefeedback, $maxscorelms, $xpathtask) {
        $this->gradinghints = $gradinghints;
        $this->testselement = $tests;
        $this->separatetestfeedback = $separatetestfeedback;
        $this->namespacegradinghints = $namespacegradinghints;
        $this->namespacefeedback = $namespacefeedback;
        $this->maxscorelms = $maxscorelms;
        $this->feedbackfiles = $feedbackfiles;
        $this->xpathtask = $xpathtask;
        $this->scorecompensationfactor = 1;

        $this->testresults = [];
        $this->files = [];

        $this->init();
    }

    private function init() {
        //Preprocess GradingScheme
        $gradingschemehandler = new grading_scheme_handler($this->gradinghints,  $this->testselement, $this->namespacegradinghints, $this->maxscorelms, $this->xpathtask);
        $gradingschemehandler->build_grading_scheme();
        $this->gradingscheme = $gradingschemehandler->get_grading_scheme();
        $this->hasgradinghintsrootchildren = $gradingschemehandler->has_gradinghintsroot_children();
        $this->scorecompensationfactor = $gradingschemehandler->get_scorecompensationfactor();

        // Preprocess test feedback.
        $testsresponses = $this->separatetestfeedback->getElementsByTagNameNS($this->namespacefeedback, "tests-response")[0];
        foreach ($testsresponses->getElementsByTagNameNS($this->namespacefeedback, "test-response") as $testresponse) {
            $subtestsresponse = $testresponse->getElementsByTagNameNS($this->namespacefeedback, 'subtests-response');
            if ($subtestsresponse->length == 0) {
                // No subtests.
                $this->testresults[$testresponse->getAttribute('id')] = $testresponse->getElementsByTagNameNS(
                                $this->namespacefeedback, 'test-result')[0];
            } else {
                // Add an array of all subtests.
                $subtests = [];
                foreach ($subtestsresponse[0]->getElementsByTagNameNS(
                        $this->namespacefeedback, 'subtest-response') as $subtestresponse) {
                    $subtests[$subtestresponse->getAttribute('id')] = $subtestresponse->getElementsByTagNameNS(
                                    $this->namespacefeedback, 'test-result')[0];
                }
                $this->testresults[$testresponse->getAttribute('id')] = $subtests;
            }
        }
        // Preprocess files.
        foreach ($this->feedbackfiles->getElementsByTagNameNS($this->namespacefeedback, "file") as $file) {
            $this->files[$file->getAttribute('id')] = $file;
        }
    }

    /**
     * This function takes the gradingscheme of the task and fills it with feedback information
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    public function process_result() {

        $this->detailedfeedback = new separate_feedback_text_node('detailed_feedback');
        $this->copy_infos_from_grading_hints_node($this->detailedfeedback, $this->gradingscheme);
        $this->detailedfeedback->set_heading(get_string('detailedfeedback', 'qtype_moopt'));

        if ($this->hasgradinghintsrootchildren) {
            $this->calculatedscore = $this->calculate_from_children($this->gradingscheme, $this->detailedfeedback);
        } else {
            $this->calculatedscore = $this->calculate_without_children($this->gradingscheme, $this->detailedfeedback);
        }

        $this->detailedfeedback->set_score($this->calculatedscore);

        $this->summarisedfeedback = new separate_feedback_text_node('summarised_feedback',
                get_string('summarizedfeedback', 'qtype_moopt'));
        $this->summarisedfeedback->set_has_internal_error($this->detailedfeedback->has_internal_error());
        $this->fill_feedback_node_with_feedback_list($this->summarisedfeedback,
                $this->separatetestfeedback->getElementsByTagNameNS($this->namespacefeedback, 'submission-feedback-list')[0]);
    }

    /**
     * @return mixed The calculated score of the submission
     */
    public function get_calculated_score() {
        return $this->calculatedscore;
    }

    /**
     * @return separate_feedback_text_node The rootnode of the separate feedback tree
     */
    public function get_detailed_feedback(): separate_feedback_text_node {
        return $this->detailedfeedback;
    }

    public function get_summarised_feedback(): separate_feedback_text_node {
        return $this->summarisedfeedback;
    }

    /**
     * Calculates the score for the submission when there are no childs in the grading hints root element.
     * In this case, the calculated score is the accumulated score of all tests of the task.
     * @param grading_hints_text_node $gradinghintsnode The root node of the grading scheme
     * @param separate_feedback_text_node $detailedfeedback The root node of the separate feedback tree
     * @return float|int|mixed The calculated score
     * @throws service_communicator_exception
     */
    private function calculate_without_children(grading_hints_text_node $gradinghintsnode, separate_feedback_text_node $detailedfeedback) {

        switch ($gradinghintsnode->get_accumulator_function()) {
            case 'min':
                $totalscore = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
            case 'max':
                $totalscore = 0.0;
                $mergefunc = 'max';
                break;
            case 'sum':
                $totalscore = 0.0;
                $mergefunc = function($a, $b) {
                    return $a + $b;
                };
                break;
        }

        foreach ($this->gradingscheme->get_children() as $child) {
            $newchild = new separate_feedback_text_node($child->get_id());
            $this->copy_infos_from_grading_hints_node($newchild, $child);
            $detailedfeedback->add_child($newchild);

            $value = $this->testresults[$child->get_id()];
            if (is_array($value)) {
                // According to the specification there must not be a subresult that is not specified in the grading hints.
                // If we are here  we don't have any grading hints at all
                // hence there musst not be any subresult.
                $id = $child->get_id();
                throw new service_communicator_exception("Grader returned subresult(s) for test result with id '$id' but there were no" .
                    " subresults specified in the grading hints. According to the specification this is invalid behaviour." .
                    " In fact there are no grading hints in the task at all.");
            } else {
                $result = $value->getElementsByTagNameNS($this->namespacefeedback, 'result')[0];
                $score = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
                $this->fill_feedback_node_with_feedback_list($newchild, $value->getElementsByTagNameNS(
                    $this->namespacefeedback, 'feedback-list')[0]);
                $newchild->set_score($score);
                $totalscore = $mergefunc($totalscore, $score);

                if ($result->hasAttribute('is-internal-error') && $result->getAttribute('is-internal-error') == "true") {
                    $newchild->set_has_internal_error(true);
                    $detailedfeedback->set_has_internal_error(true);
                }
            }
        }

        return $totalscore;
    }

    /**
     * This function will build a subtree of separate_feedback_text_nodes that represents a part of the
     * separate feedback tree. It will calculate the score of the separate feedback subtree.
     * @param grading_hints_text_node $gradinghintsnode The root node of the grading scheme subtree
     * @param separate_feedback_text_node $detailedfeedback The root node of the separate feedback subtree
     * @param bool $scalescoretolms
     * @return int|mixed The calculated score
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function calculate_from_children(grading_hints_text_node $gradinghintsnode, separate_feedback_text_node $detailedfeedback,
                                                                     $scalescoretolms = true) {
        switch ($gradinghintsnode->get_accumulator_function()) {
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

        $counter = 0;
        $internalerrorinchildren = false;

        foreach ($gradinghintsnode->get_children() as $id => $child) {
            $newchild = new separate_feedback_text_node($detailedfeedback->get_id() . '_' . $counter++);
            $this->copy_infos_from_grading_hints_node($newchild, $child);
            $detailedfeedback->add_child($newchild);
            if ($child->get_type() === 'test') {
                $score = $this->get_weighted_score_testref($child, $newchild, $scalescoretolms);

                $newchild->set_score($score);

                $value = $mergefunc($value, $score);

                if ($newchild->has_internal_error()) {
                    $internalerrorinchildren = true;
                }
            } elseif ($child->get_type() === 'combine') {
                $newchild->set_heading(get_string('combinedresult', 'qtype_moopt'));

                $score = $this->get_weighted_score_combineref($child, $newchild, $scalescoretolms);

                $newchild->set_score($score);

                $value = $mergefunc($value, $score);

                if ($newchild->has_internal_error()) {
                    $internalerrorinchildren = true;
                }
            }
        }
        if ($internalerrorinchildren) {
            $detailedfeedback->set_has_internal_error(true);
        }

        return $value;
    }

    /**
     * Fills the separate feedback tree with infos of the test and calculates the weighted score of the test
     * @param grading_hints_text_node $gradinghintsnode The grading hints node that represents the test
     * @param separate_feedback_text_node $detailedfeedbacknode The separate feedback node that represents the test
     * @param bool $scalescoretolms
     * @return float|int The calculated score of the test
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function get_weighted_score_testref(grading_hints_text_node $gradinghintsnode, separate_feedback_text_node $detailedfeedbacknode, $scalescoretolms = true) {
        $refid = $gradinghintsnode->get_refid();

        if(isset($this->testresults[$refid])) {
            if ($gradinghintsnode->get_subref() !== null && $gradinghintsnode->get_subref() !== '') {
                $tmp = $this->testresults[$refid];
                if (is_array($tmp) && isset($tmp[$gradinghintsnode->get_subref()])) {
                    $testresult = $tmp[$gradinghintsnode->get_subref()];
                } else {
                    // The ProFormA whitepaper allows test results without sub results even if the grading hints
                    // section has sub-ref references. A common use case is that the grader cannot start the
                    // respective test at all so it doesn't make sense to report individual sub-test results,
                    // because none of sub-tests has succeeded.
                    $testresult = $tmp;
                }
            } else {
                $testresult = $this->testresults[$refid];
                if (is_array($testresult)) {
                    throw new service_communicator_exception("Grader returned subresult(s) for test result with id '$refid' but there were no" .
                        " subresults specified in the grading hints. According to the specification this is invalid behaviour");
                }
            }
        }
        if (isset($testresult)) {
            $result = $testresult->getElementsByTagNameNS($this->namespacefeedback, 'result')[0];
            $score = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
            $this->fill_feedback_node_with_testresult_infos($detailedfeedbacknode, $testresult);
        } else {
            $result = null;
            $score = 0;
        }
        $detailedfeedbacknode->set_heading(get_string('test', 'qtype_moopt') . ' ');

        if (isset($result) && $result->hasAttribute('is-internal-error')) {
            $detailedfeedbacknode->set_has_internal_error($result->getAttribute('is-internal-error') == "true");
        }

        // Execute functions and only later set score to 0 because the above functions also fills the feedback elements.
        if ($this->should_be_nullified_node($gradinghintsnode)) {
            $score = 0;
            $detailedfeedbacknode->set_nullified(true);
        }

        $detailedfeedbacknode->set_rawscore($score);

        if ($scalescoretolms) {
            return $score * $gradinghintsnode->get_weight() * $this->scorecompensationfactor;
        } else {
            return $score * $gradinghintsnode->get_weight();
        }
    }

    /**
     * Fills the separate feedback tree with infos of the combined test and calculates the weighted score of the combined test.
     * It processes all child elements of the combined test
     * @param grading_hints_text_node $gradinghintsnode
     * @param separate_feedback_text_node $node
     * @param bool $scalescoretolms
     * @return float|int
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function get_weighted_score_combineref(grading_hints_text_node $gradinghintsnode, separate_feedback_text_node $node, $scalescoretolms = true) {
        $score = $this->calculate_from_children($gradinghintsnode, $node, $scalescoretolms);
        // Execute function and only later set score to 0 because the above function also processes the child elements.
        if ($this->should_be_nullified_node($gradinghintsnode)) {
            $score = 0;
            $node->set_nullified(true);
        }
        $node->set_rawscore($score);
        return $score * $gradinghintsnode->get_weight();
    }

    private function fill_feedback_node_with_testresult_infos(separate_feedback_text_node $node,
                                                              ?\DOMElement $testresult) {
        if (isset($testresult)) {
            $this->fill_feedback_node_with_feedback_list($node, $testresult->getElementsByTagNameNS($this->namespacefeedback,
                            'feedback-list')[0]);
        }
    }

    private function fill_feedback_node_with_feedback_list(separate_feedback_text_node $node, \DOMElement $feedbacklist) {
        foreach ($feedbacklist->getElementsByTagNameNS($this->namespacefeedback, 'student-feedback') as $studfeedback) {
            $node->add_student_feedback(
                    [
                        "title" => ($tmp = $studfeedback->getElementsByTagNameNS($this->namespacefeedback, 'title'))->length == 1 ?
                                $tmp[0]->nodeValue : null,
                        "content" => (
                        $tmp = $studfeedback->getElementsByTagNameNS($this->namespacefeedback, 'content'))->length == 1 ?
                                $tmp[0]->nodeValue : null,
                        "files" => $this->extract_file_infos_from_filerefs($studfeedback)
                    ]
            );
        }
        foreach ($feedbacklist->getElementsByTagNameNS($this->namespacefeedback, 'teacher-feedback') as $teachfeedback) {
            $node->add_teacher_feedback(
                    [
                        "title" => ($tmp = $teachfeedback->getElementsByTagNameNS($this->namespacefeedback, 'title'))->length == 1 ?
                                $tmp[0]->nodeValue : null,
                        "content" => (
                        $tmp = $teachfeedback->getElementsByTagNameNS($this->namespacefeedback, 'content'))->length == 1 ?
                                $tmp[0]->nodeValue : null,
                        "files" => $this->extract_file_infos_from_filerefs($teachfeedback)
                    ]
            );
        }
    }

    private function extract_file_infos_from_filerefs(\DOMElement $elem) {
        $embeddedfiles = [];
        $attachedfiles = [];
        if (($tmp = $elem->getElementsByTagNameNS($this->namespacefeedback, 'filerefs'))->length == 1) {
            $filerefs = $tmp[0];
            foreach ($filerefs->getElementsByTagNameNS($this->namespacefeedback, 'fileref') as $fileref) {
                $refid = $fileref->getAttribute('refid');
                $file = $this->files[$refid];

                $elem = null;
                if (($tmp = $file->getElementsByTagNameNS($this->namespacefeedback, 'embedded-bin-file'))->length == 1) {
                    $elem = $tmp[0];
                } else if (($tmp = $file->getElementsByTagNameNS($this->namespacefeedback, 'embedded-txt-file'))->length == 1) {
                    $elem = $tmp[0];
                }
                if ($elem != null) {
                    // Embedded file.
                    $embeddedfiles[] = ['id' => $refid, 'filename' => $elem->getAttribute('filename'),
                        'title' => $file->getAttribute('title')];
                    continue;
                }

                if (($tmp = $file->getElementsByTagNameNS($this->namespacefeedback, 'attached-bin-file'))->length == 1) {
                    $elem = $tmp[0];
                } else if (($tmp = $file->getElementsByTagNameNS($this->namespacefeedback, 'attached-txt-file'))->length == 1) {
                    $elem = $tmp[0];
                }
                $attachedfiles[] = ['filename' => $elem->nodeValue, 'title' => $file->getAttribute('title')];
            }
        }
        return ["embeddedFiles" => $embeddedfiles, "attachedFiles" => $attachedfiles];
    }

    /**
     * Copies all grading scheme related information from a grading hints node in to a separate feedback node
     * @param separate_feedback_text_node $targetnode
     * @param grading_hints_text_node $sourcenode
     */
    private function copy_infos_from_grading_hints_node(separate_feedback_text_node $targetnode, grading_hints_text_node $sourcenode) {
        $targetnode->set_accumulator_function($sourcenode->get_accumulator_function());
        $targetnode->set_heading($sourcenode->get_heading());
        $targetnode->set_description($sourcenode->get_description());
        $targetnode->set_internal_description($sourcenode->get_internal_description());
        $targetnode->set_max_score($sourcenode->get_max_score());
        $targetnode->set_title($sourcenode->get_title());
        $targetnode->set_weight($sourcenode->get_weight());
        $targetnode->set_nullifyconditionroot($sourcenode->get_nullifyconditionroot());
        $targetnode->set_type($sourcenode->get_type());
        $targetnode->set_refid($sourcenode->get_refid());
        $targetnode->set_subref($sourcenode->get_subref());
    }

    // Nullifying from here on.

    /**
     * Checks if the score of the separate feedback node should be nullified
     * @param grading_hints_text_node $node The corresponding node to the separate feedback node
     * @return bool
     */
    private function should_be_nullified_node(grading_hints_text_node $node): bool {
        if ($node->get_nullifyconditionroot() !== null) {
            $nullifyconditionroot = $node->get_nullifyconditionroot();
            switch (get_class($nullifyconditionroot)) {
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition':
                    return $this->should_be_nullified_single($nullifyconditionroot);
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions':
                    return $this->should_be_nullified_composite($nullifyconditionroot);
            }
        }
        return false;
    }

    /**
     * Checks a simple nullifycondition
     * @param grading_hints_nullify_condition $nullifycondition The nullifycondition to check
     * @return bool
     */
    private function should_be_nullified_single(grading_hints_nullify_condition $nullifycondition): bool {
        $values = $this->get_nullify_values($nullifycondition);
        switch ($nullifycondition->get_compareoperator()) {
            case 'eq':
                return $values[0] == $values[1];
            case 'ne':
                return $values[0] != $values[1];
            case 'gt':
                return $values[0] > $values[1];
            case 'ge':
                return $values[0] >= $values[1];
            case 'lt':
                return $values[0] < $values[1];
            case 'le':
                return $values[0] <= $values[1];
            default:
                return false;
        }
    }

    /**
     * @param grading_hints_nullify_condition $nullifycondition
     * @return array An array with the values of both nullifycondition operands
     */
    private function get_nullify_values(grading_hints_nullify_condition $nullifycondition): array {
        $values = [];
        $operands = array($nullifycondition->get_leftoperand(), $nullifycondition->get_rightoperand());
        foreach ($operands as $operand) {
            if (is_float($operand)) {
                //Operand is a literal value
                $values[] = $operand;
                continue;
            }
            switch (get_class($operand)) {
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_combineref_operand':
                    $values[] = $this->get_nullify_combine_value($operand);
                    break;
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition_testref_operand':
                    $values[] = $this->get_nullify_test_value($operand);
                    break;
            }
        }
        return $values;
    }

    /**
     * Calculates the score of the given combineref
     * @param grading_hints_nullify_condition_combineref_operand $operand The combineref operand from which to calculate the score
     * @return float The score of the given combineref
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function get_nullify_combine_value(grading_hints_nullify_condition_combineref_operand $operand): float {
        $refid = $operand->get_ref();
        $gradinghintsnode = $this->gradingscheme->get_child_by_refid($refid);
        $score = $this->calculate_from_children($gradinghintsnode,
                new separate_feedback_text_node('dummy') /* This is just a dummy object */, false);
        return $score;
    }

    /**
     * Calculates the score of a given testref
     * @param grading_hints_nullify_condition_testref_operand $operand The testref operand from which to calculate the score
     * @return float The score of the given testref
     */
    private function get_nullify_test_value(grading_hints_nullify_condition_testref_operand $operand): float {
        $refid = $operand->get_ref();
        if ($operand->get_subref() !== null && $operand->get_subref() !== '') {
            $testresult = $this->testresults[$refid][$operand->get_subref()];
        } else {
            $testresult = $this->testresults[$refid];
        }
        $score = $testresult->getElementsByTagNameNS($this->namespacefeedback, 'result')[0]->getElementsByTagNameNS(
                        $this->namespacefeedback, 'score')[0]->nodeValue;
        return $score;
    }

    /**
     * Checks a composite nullifycondition
     * @param grading_hints_nullify_conditions $nullifyconditions The composite nullifycondition to check
     * @return bool
     */
    private function should_be_nullified_composite(grading_hints_nullify_conditions $nullifyconditions): bool {
        if ($nullifyconditions->get_composeoperator() == 'and') {
            $value = true;
            $mergefunc = function($prev, $next) {
                return $prev && $next;
            };
        } else {
            $value = false;
            $mergefunc = function($prev, $next) {
                return $prev || $next;
            };
        }
        foreach ($nullifyconditions->get_operands() as $operand) {
            switch(get_class($operand)) {
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_condition':
                    $value = $mergefunc($value, $this->should_be_nullified_single($operand));
                    break;
                case 'qtype_moopt\utility\proforma_xml\grading_hints_nullify_conditions':
                    $value = $mergefunc($value, $this->should_be_nullified_composite($operand));
                    break;
            }
        }
        return $value;
    }
}
