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
        $this->detailedfeedback = $gradingschemehandler->get_grading_scheme();
        $this->detailedfeedback->addSeparateFeedbackData();
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
        $this->detailedfeedback->set_heading(get_string('detailedfeedback', 'qtype_moopt'));

        if ($this->hasgradinghintsrootchildren) {
            $this->calculatedscore = $this->fill_node_with_feedback_data_from_children($this->detailedfeedback);
        } else {
            $this->calculatedscore = $this->fill_with_feedback_no_children($this->detailedfeedback);
        }

        $this->detailedfeedback->getSeparateFeedbackData()->set_score($this->calculatedscore);

        $this->summarisedfeedback = new grading_hints_node('summarised_feedback',
                get_string('summarizedfeedback', 'qtype_moopt'));
        $this->summarisedfeedback->addSeparateFeedbackData();
        $this->summarisedfeedback->getSeparateFeedbackData()->set_has_internal_error($this->detailedfeedback->getSeparateFeedbackData()->has_internal_error());
        $this->fill_feedback_node_with_feedback_list($this->summarisedfeedback->getSeparateFeedbackData(),
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
    public function get_detailed_feedback(): grading_hints_node {
        return $this->detailedfeedback;
    }

    public function get_summarised_feedback(): grading_hints_node {
        return $this->summarisedfeedback;
    }

    /**
     * Fills the grading hints tree with the feedback information in case there are no childs in the grading hints root element.
     * The calculated score is the accumulated score of all tests of the task.
     * @param grading_hints_node $rootnode
     * @return mixed
     */
    public function fill_with_feedback_no_children(grading_hints_node $rootnode) {
        list("maxvalue" => $totalscore, "mergefunc" => $mergefunc) = $this->get_merge_variables($rootnode->get_accumulator_function());
        foreach ($this->detailedfeedback->get_children() as $child) {
            $child->addSeparateFeedbackData();

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
                $this->fill_feedback_node_with_feedback_list($child->getSeparateFeedbackData(), $value->getElementsByTagNameNS(
                    $this->namespacefeedback, 'feedback-list')[0]);

                if ($result->hasAttribute('is-internal-error') && $result->getAttribute('is-internal-error') == "true") {
                    $child->getSeparateFeedbackData()->set_has_internal_error(true);
                    $rootnode->getSeparateFeedbackData()->set_has_internal_error(true);
                }
                $result = $value->getElementsByTagNameNS($this->namespacefeedback, 'result')[0];
                $score = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
                $child->getSeparateFeedbackData()->set_score($score);
                $totalscore = $mergefunc($totalscore, $score);
            }
        }
        return $totalscore;
    }

    /**
     * This function will build a subtree of separate_feedback_text_nodes that represents a part of the
     * separate feedback tree. It will calculate the score of the separate feedback subtree.
     * @param grading_hints_node $rootnode The root node of the grading scheme subtree
     * @return int|mixed The calculated score
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function fill_node_with_feedback_data_from_children(grading_hints_node $rootnode) {
        list("maxvalue" => $value, "mergefunc" => $mergefunc) = $this->get_merge_variables($rootnode->get_accumulator_function());
        $internalerrorinchildren = false;
        foreach ($rootnode->get_children() as $id => $child) {
            $child->addSeparateFeedbackData();
            if ($child->get_type() === 'test') {
                $score = $this->fill_test_node_with_feedback_data($child);
                $value = $mergefunc($value, $score);
                if ($child->getSeparateFeedbackData()->has_internal_error()) {
                    $internalerrorinchildren = true;
                }
            } elseif ($child->get_type() === 'combine') {
                $rawscore = $this->fill_node_with_feedback_data_from_children($child);

                $child->getSeparateFeedbackData()->set_rawscore($rawscore);

                // Execute function and only later set score to 0 because the above function also processes the child elements.
                if ($this->should_be_nullified_node($child)) {
                    $rawscore = 0;
                    $child->getSeparateFeedbackData()->set_nullified(true);
                }

                $child->getSeparateFeedbackData()->set_score($rawscore);
                $value = $mergefunc($value, $rawscore);
                if ($child->getSeparateFeedbackData()->has_internal_error()) {
                    $internalerrorinchildren = true;
                }
            }
        }
        if ($internalerrorinchildren) {
            $rootnode->getSeparateFeedbackData()->set_has_internal_error(true);
        }
        return $value;
    }

    /**
     * Fills the separate feedback tree with infos of the test and calculates the weighted score of the test
     * @param grading_hints_node $gradinghintsnode The grading hints node that represents the test
     * @return float|int The calculated score of the test
     * @throws \coding_exception
     * @throws service_communicator_exception
     */
    private function fill_test_node_with_feedback_data(grading_hints_node $gradinghintsnode) {
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
            $rawscore = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
            $this->fill_feedback_node_with_testresult_infos($gradinghintsnode->getSeparateFeedbackData(), $testresult);
        } else {
            $result = null;
            $rawscore = 0;
        }
        $gradinghintsnode->set_heading(get_string('test', 'qtype_moopt') . ' ');

        if (isset($result) && $result->hasAttribute('is-internal-error')) {
            $gradinghintsnode->getSeparateFeedbackData()->set_has_internal_error($result->getAttribute('is-internal-error') == "true");
        }

        // Execute functions and only later set score to 0 because the above functions also fills the feedback elements.
        if ($this->should_be_nullified_node($gradinghintsnode)) {
            $rawscore = 0;
            $gradinghintsnode->getSeparateFeedbackData()->set_nullified(true);
        }

        $gradinghintsnode->getSeparateFeedbackData()->set_rawscore($rawscore);

        $score = $rawscore * $gradinghintsnode->get_weight() * $this->scorecompensationfactor;

        $gradinghintsnode->getSeparateFeedbackData()->set_score($score);
        return $score;
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
     * @param $accumulatorfunction
     * @return array An array containing two fields: maxvalue and mergefunc
     */
    private function get_merge_variables($accumulatorfunction) : array {
        switch ($accumulatorfunction) {
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
        return array("maxvalue" => $maxvalue, "mergefunc" => $mergefunc);
    }

    // Nullifying from here on.

    /**
     * Checks if the score of the separate feedback node should be nullified
     * @param grading_hints_node $node The corresponding node to the separate feedback node
     * @return bool
     */
    private function should_be_nullified_node(grading_hints_node $node): bool {
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
        $gradinghintsnode = $this->detailedfeedback->get_child_by_refid($refid);
        return $gradinghintsnode->getSeparateFeedbackData()->get_score();
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
