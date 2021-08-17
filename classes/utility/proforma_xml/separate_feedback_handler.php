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

namespace qtype_programmingtask\utility\proforma_xml;

use qtype_programmingtask\exceptions\service_communicator_exception;

defined('MOODLE_INTERNAL') || die();

class separate_feedback_handler {

    private $namespacegradinghints;
    private $namespacefeedback;
    private $gradinghints;
    private $testselement;
    private $separatetestfeedback;
    private $gradinghintscombines;
    private $gradinghintsroot;
    private $testresults;
    private $tests;
    private $maxscoregradinghints;
    private $maxscorelms;
    private $scorecompensationfactor;
    private $calculatedscore;
    private $detailedfeedback;
    private $summarisedfeedback;
    private $files;
    private $feedbackfiles;
    private $xpathtask;
    private $xpathresponse;

    public function __construct($gradinghints, \DOMElement $tests, \DOMElement $separatetestfeedback, \DOMElement $feedbackfiles,
            $namespacegradinghints, $namespacefeedback, $maxscorelms, $xpathtask, $xpathresponse) {
        $this->gradinghints = $gradinghints;
        $this->testselement = $tests;
        $this->separatetestfeedback = $separatetestfeedback;
        $this->namespacegradinghints = $namespacegradinghints;
        $this->namespacefeedback = $namespacefeedback;
        $this->maxscorelms = $maxscorelms;
        $this->feedbackfiles = $feedbackfiles;
        $this->xpathtask = $xpathtask;
        $this->xpathresponse = $xpathresponse;
        $this->scorecompensationfactor = 1;

        $this->gradinghintscombines = [];
        $this->testresults = [];
        $this->tests = [];
        $this->files = [];

        $this->init();
    }

    private function init() {
        // Preprocess grading hints.
        if ($this->gradinghints != null) {
            $this->gradinghintsroot = $this->gradinghints->getElementsByTagNameNS($this->namespacegradinghints, "root")[0];
            foreach ($this->gradinghints->getElementsByTagNameNS($this->namespacegradinghints, "combine") as $combine) {
                $this->gradinghintscombines[$combine->getAttribute('id')] = $combine;
            }
        }
        // Preprocess tests.
        foreach ($this->testselement->getElementsByTagNameNS($this->namespacegradinghints, "test") as $test) {
            $this->tests[$test->getAttribute('id')] = $test;
        }
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

        if ($this->gradinghintsroot != null && $this->has_children($this->gradinghintsroot)) {
            $gradinghintshelper = new grading_hints_helper($this->gradinghints, $this->testselement, $this->namespacegradinghints);
            $this->maxscoregradinghints = $gradinghintshelper->calculate_max_score();
            if (abs($this->maxscoregradinghints - $this->maxscorelms) > 1e-5) {
                // - scorecompensationfactor is the scaling value for all student and max scores
                // - maxscorelms is the question's default mark
                $this->scorecompensationfactor = $this->maxscorelms / $this->maxscoregradinghints;
            }
        }
    }

    public function process_result() {

        $this->detailedfeedback = new separate_feedback_text_node('detailed_feedback',
                get_string('detailedfeedback', 'qtype_programmingtask'));
        if ($this->gradinghintsroot != null && $this->has_children($this->gradinghintsroot)) {
            $this->fill_feedback_with_combine_node_infos($this->gradinghintsroot, $this->detailedfeedback);
            list($this->calculatedscore, $maxscore) = $this->calculate_from_children($this->gradinghintsroot,
                    $this->detailedfeedback);
            $this->detailedfeedback->set_max_score($maxscore);
        } else {
            $this->calculatedscore = $this->calculate_without_children($this->gradinghintsroot, $this->detailedfeedback);
        }

        $this->detailedfeedback->set_score($this->calculatedscore);

        $this->summarisedfeedback = new separate_feedback_text_node('summarised_feedback',
                get_string('summarizedfeedback', 'qtype_programmingtask'));
        $this->fill_feedback_node_with_feedback_list($this->summarisedfeedback,
                $this->separatetestfeedback->getElementsByTagNameNS($this->namespacefeedback, 'submission-feedback-list')[0]);
    }

    public function get_calculated_score() {
        return $this->calculatedscore;
    }

    public function get_detailed_feedback(): separate_feedback_text_node {
        return $this->detailedfeedback;
    }

    public function get_summarised_feedback(): separate_feedback_text_node {
        return $this->summarisedfeedback;
    }

    private function has_children(\DOMElement $elem) {
        return $elem->getElementsByTagNameNS($this->namespacegradinghints, 'test-ref')->length +
                $elem->getElementsByTagNameNS($this->namespacegradinghints, 'combine-ref')->length != 0;
    }

    private function calculate_without_children($elem, separate_feedback_text_node $detailedfeedback) {

        $function = "min";

        if ($elem != null) {
            if (($list = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'description'))->length == 1) {
                $detailedfeedback->set_description($list[0]->nodeValue);
            }
            if (($list = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'internal-description'))->length == 1) {
                $detailedfeedback->set_internal_description($list[0]->nodeValue);
            }

            if ($elem->hasAttribute('function')) {
                $function = $elem->getAttribute('function');
            }
        }

        switch ($function) {
            case 'min':
                $initialvalue = $totalscore = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
            case 'max':
                $initialvalue = $totalscore = 0.0;
                $mergefunc = 'max';
                break;
            case 'sum':
                $initialvalue = $totalscore = 0.0;
                $mergefunc = function($a, $b) {
                    return $a + $b;
                };
                break;
        }
        $detailedfeedback->set_accumulator_function($function);

        foreach ($this->testresults as $key => $value) {

            $detfeed = new separate_feedback_text_node($key);
            $detailedfeedback->add_child($detfeed);

            // Get infos from tests.
            $detfeed->set_title($this->tests[$key]->getElementsByTagNameNS($this->namespacegradinghints, 'title')[0]->nodeValue);
            if (($list = $this->tests[$key]->getElementsByTagNameNS($this->namespacegradinghints, 'description'))->length == 1) {
                $detfeed->set_description($list[0]->nodeValue);
            }
            if (($list = $this->tests[$key]->getElementsByTagNameNS(
                    $this->namespacegradinghints, 'internal-description'))->length == 1) {
                $detfeed->set_internal_description($list[0]->nodeValue);
            }

            if (is_array($value)) {
                // According to the specification there musst not be a subresult that is not specified in the grading hints.
                // If we are here  we don't have any grading hints at all
                // hence there musst not be any subresult.
                throw new service_communicator_exception("Grader returned subresult(s) for test result with id '$key' but there were no" .
                        " subresults specified in the grading hints. According to the specification this is invalid behaviour." .
                        " In fact there are no grading hints in the task at all.");
            } else {
                $detfeed->set_heading(get_string('test', 'qtype_programmingtask'));
                $result = $value->getElementsByTagNameNS($this->namespacefeedback, 'result')[0];
                $score = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
                $this->fill_feedback_node_with_feedback_list($detfeed, $value->getElementsByTagNameNS(
                                $this->namespacefeedback, 'feedback-list')[0]);
                $detfeed->set_score($score);
                $totalscore = $mergefunc($totalscore, $score);

                if ($result->hasAttribute('is-internal-error') && $result->getAttribute('is-internal-error') == "true") {
                    $detfeed->set_has_internal_error(true);
                    $detailedfeedback->set_has_internal_error(true);
                }
            }
        }

        return $totalscore;
    }

    private function calculate_from_children(\DOMElement $elem, separate_feedback_text_node $detailedfeedback,
            $scalescoretolms = true) {
        $function = "min";
        if ($elem->hasAttribute('function')) {
            $function = $elem->getAttribute('function');
        }
        switch ($function) {
            case 'min':
                $value = $maxvalue = PHP_INT_MAX;
                $mergefunc = 'min';
                break;
            case 'max':
                $value = $maxvalue = 0;
                $mergefunc = 'max';
                break;
            case 'sum':
                $value = $maxvalue = 0;
                $mergefunc = function($a, $b) {
                    return $a + $b;
                };
                break;
        }
        $detailedfeedback->set_accumulator_function($function);
        $counter = 0;
        $internalerrorinchildren = false;

        foreach ($elem->getElementsByTagNameNS($this->namespacegradinghints, 'test-ref') as $testref) {
            $detfeed = new separate_feedback_text_node($detailedfeedback->get_id() . '_' . $counter++);
            $detailedfeedback->add_child($detfeed);

            list($score, $maxscore) = $this->get_weighted_score_testref($testref, $detfeed, $scalescoretolms);
            // Execute function and only later set score to 0 because the above function also fills the feedback elements.
            if ($this->should_be_nullified_elem($testref)) {
                $score = 0;
                $detfeed->set_nullified(true);
            }
            $detfeed->set_score($score);
            $detfeed->set_max_score($maxscore);

            $value = $mergefunc($value, $score);
            $maxvalue = $mergefunc($maxvalue, $maxscore);

            if ($detfeed->has_internal_error()) {
                $internalerrorinchildren = true;
            }
        }
        foreach ($elem->getElementsByTagNameNS($this->namespacegradinghints, 'combine-ref') as $combineref) {
            $detfeed = new separate_feedback_text_node($detailedfeedback->get_id() . '_' . $counter++);
            $detailedfeedback->add_child($detfeed);

            $detfeed->set_heading(get_string('combinedresult', 'qtype_programmingtask'));
            $this->fill_feedback_with_combine_node_infos($this->gradinghintscombines[$combineref->getAttribute('ref')], $detfeed);

            list($score, $maxscore) = $this->get_weighted_score_combineref($combineref, $detfeed, $scalescoretolms);
            // Execute function and only later set score to 0 because the above function also processes the child elements.
            if ($this->should_be_nullified_elem($combineref)) {
                $score = 0;
                $detfeed->set_nullified(true);
            }
            $detfeed->set_score($score);
            $detfeed->set_max_score($maxscore);

            $value = $mergefunc($value, $score);
            $maxvalue = $mergefunc($maxvalue, $maxscore);

            if ($detfeed->has_internal_error()) {
                $internalerrorinchildren = true;
            }
        }

        if ($internalerrorinchildren) {
            $detailedfeedback->set_has_internal_error(true);
        }

        return [$value, $maxvalue];
    }

    private function get_weighted_score_combineref(\DOMElement $elem, separate_feedback_text_node $detailedfeedbacknode,
            $scalescoretolms = true) {
        $refid = $elem->getAttribute('ref');
        $combine = $this->gradinghintscombines[$refid];
        list($score, $maxscore) = $this->calculate_from_children($combine, $detailedfeedbacknode, $scalescoretolms);
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        return [$score * $weight, $maxscore * $weight];
    }

    private function fill_feedback_with_combine_node_infos(\DOMElement $elem, separate_feedback_text_node $detailedfeedbacknode) {
        if (($list = $this->xpathtask->query('./p:title', $elem))->length == 1) {
            $detailedfeedbacknode->set_title($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:description', $elem))->length == 1) {
            $detailedfeedbacknode->set_description($list[0]->nodeValue);
        }
        if (($list = $this->xpathtask->query('./p:internal-description', $elem))->length == 1) {
            $detailedfeedbacknode->set_internal_description($list[0]->nodeValue);
        }
    }

    private function get_weighted_score_testref(\DOMElement $elem, separate_feedback_text_node $detailedfeedbacknode,
            $scalescoretolms = true) {
        $refid = $elem->getAttribute('ref');

        if(!isset($this->testresults[$refid])) {
            throw new \Exception("Missing test-response: The response file does not contain a test-response with ID '".$refid."'.");
        }

        if ($elem->hasAttribute('sub-ref')) {
            if(!isset($this->testresults[$refid][$elem->getAttribute('sub-ref')])) {
                // TODO: get_string
                $errormsg = "Missing subtest-response: The response file does not contain a subtest-response with ID '" . $elem->getAttribute('sub-ref') . "' in test-response with ID '" . $refid . "'.";
                throw new \Exception($errormsg);
            }
            $testresult = $this->testresults[$refid][$elem->getAttribute('sub-ref')];
        } else {
            $testresult = $this->testresults[$refid];
            if (is_array($testresult)) {
                throw new service_communicator_exception("Grader returned subresult(s) for test result with id '$refid' but there were no" .
                        " subresults specified in the grading hints. According to the specification this is invalid behaviour");
            }
        }

        $result = $testresult->getElementsByTagNameNS($this->namespacefeedback, 'result')[0];
        $score = $result->getElementsByTagNameNS($this->namespacefeedback, 'score')[0]->nodeValue;
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $detailedfeedbacknode->set_heading(get_string('test', 'qtype_programmingtask') . ' ');
        $this->fill_feedback_node_with_test_infos($elem, $detailedfeedbacknode, $testresult);

        if ($result->hasAttribute('is-internal-error')) {
            $detailedfeedbacknode->set_has_internal_error($result->getAttribute('is-internal-error') == "true");
        }

        if ($scalescoretolms) {
            return [$score * $weight * $this->scorecompensationfactor, $weight * $this->scorecompensationfactor];
        } else {
            return [$score * $weight, $weight];
        }
    }

    private function fill_feedback_node_with_test_infos(\DOMElement $elem, separate_feedback_text_node $node,
            \DOMElement $testresult) {
        $refid = $elem->getAttribute('ref');
        if (($titlelist = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'title'))->length == 1) {
            $node->set_title($titlelist[0]->nodeValue);
        } else {
            $node->set_title($this->tests[$refid]->getElementsByTagNameNS($this->namespacegradinghints, 'title')[0]->nodeValue);
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'description'))->length == 1) {
            $node->set_description($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS($this->namespacegradinghints, 'description'))->length == 1) {
                $node->set_description($list[0]->nodeValue);
            }
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'internal-description'))->length == 1) {
            $node->set_internal_description($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS(
                    $this->namespacegradinghints, 'internal-description'))->length == 1) {
                $node->set_internal_description($list[0]->nodeValue);
            }
        }

        $this->fill_feedback_node_with_feedback_list($node, $testresult->getElementsByTagNameNS($this->namespacefeedback,
                        'feedback-list')[0]);
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

    // Nullifying from here on.

    private function should_be_nullified_elem(\DOMElement $elem): bool {
        $nullifyconditionlist = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'nullify-condition');
        if ($nullifyconditionlist->length == 1) {
            $nullifycondition = $nullifyconditionlist[0];
            return $this->should_be_nullified_single($nullifycondition);
        }
        $nullifyconditionslist = $elem->getElementsByTagNameNS($this->namespacegradinghints, 'nullify-conditions');
        if ($nullifyconditionslist->length == 1) {
            $nullifyconditions = $nullifyconditionslist[0];
            return $this->should_be_nullified_composite($nullifyconditions);
        }
        return false;
    }

    private function should_be_nullified_single(\DOMElement $elem): bool {
        $values = $this->get_nullify_values($elem);
        switch ($elem->getAttribute('compare-op')) {
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
        }
    }

    private function get_nullify_values(\DOMElement $elem): array {
        $values = [];
        foreach ($elem->childNodes as $childnode) {
            if ($childnode->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            switch ($childnode->localName) {
                case 'nullify-combine-ref':
                    $values[] = $this->get_nullify_combine_value($childnode);
                    break;
                case 'nullify-test-ref':
                    $values[] = $this->get_nullify_test_value($childnode);
                    break;
                case 'nullify-literal':
                    $values[] = $this->get_nullify_literal_value($childnode);
                    break;
            }
            if (count($values) == 2) {
                return $values;
            }
        }
    }

    private function get_nullify_combine_value(\DOMElement $elem): float {
        $refid = $elem->getAttribute('ref');
        $combine = $this->gradinghintscombines[$refid];
        list($score, $maxscore) = $this->calculate_from_children($combine,
                new separate_feedback_text_node('dummy') /* This is just a dummy object */, false);
        return $score;
    }

    private function get_nullify_test_value(\DOMElement $elem): float {
        $refid = $elem->getAttribute('ref');
        if ($elem->hasAttribute('sub-ref')) {
            $testresult = $this->testresults[$refid][$elem->getAttribute('sub-ref')];
        } else {
            $testresult = $this->testresults[$refid];
        }
        $score = $testresult->getElementsByTagNameNS($this->namespacefeedback, 'result')[0]->getElementsByTagNameNS(
                        $this->namespacefeedback, 'score')[0]->nodeValue;
        return $score;
    }

    private function get_nullify_literal_value(\DOMElement $elem): float {
        return $elem->getAttribute('value');
    }

    private function should_be_nullified_composite(\DOMElement $elem): bool {
        if ($elem->getAttribute('compose-op') == 'and') {
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
        foreach ($elem->getElementsByTagNameNS($this->namespacegradinghints, 'nullify-conditions') as $conditions) {
            $value = $mergefunc($value, $this->should_be_nullified_composite($conditions));
        }
        foreach ($elem->getElementsByTagNameNS($this->namespacegradinghints, 'nullify-condition') as $condition) {
            $value = $mergefunc($value, $this->should_be_nullified_single($condition));
        }
        return $value;
    }

}
