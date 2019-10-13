<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

defined('MOODLE_INTERNAL') || die();

class separate_feedback_handler {

    private $namespace_gradinghints;
    private $namespace_feedback;
    private $grading_hints;
    private $tests_element;
    private $separate_test_feedback;
    private $grading_hints_combines;
    private $grading_hints_root;
    private $test_results;
    private $tests;
    private $max_score_grading_hints;
    private $max_score_lms;
    private $score_compensation_factor;
    private $calculatedScore;
    private $detailedFeedback;
    private $summarisedFeedback;
    private $files;
    private $feedback_files;

    public function __construct(\DOMElement $grading_hints, \DOMElement $tests, \DOMElement $separate_test_feedback, \DOMElement $feedback_files, $namespace_gradinghints, $namespace_feedback, $max_score_lms) {
        $this->grading_hints = $grading_hints;
        $this->tests_element = $tests;
        $this->separate_test_feedback = $separate_test_feedback;
        $this->namespace_gradinghints = $namespace_gradinghints;
        $this->namespace_feedback = $namespace_feedback;
        $this->max_score_lms = $max_score_lms;
        $this->feedback_files = $feedback_files;
        $this->score_compensation_factor = 1;

        $this->grading_hints_combines = [];
        $this->test_results = [];
        $this->tests = [];
        $this->files = [];

        $this->init();
    }

    private function init() {
        //Preprocess grading hints
        $this->grading_hints_root = $this->grading_hints->getElementsByTagNameNS($this->namespace_gradinghints, "root")[0];
        foreach ($this->grading_hints->getElementsByTagNameNS($this->namespace_gradinghints, "combine") as $combine) {
            $this->grading_hints_combines[$combine->getAttribute('id')] = $combine;
        }
        //Preprocess tests
        foreach ($this->tests_element->getElementsByTagNameNS($this->namespace_gradinghints, "test") as $test) {
            $this->tests[$test->getAttribute('id')] = $test;
        }
        //Preprocess test feedback
        $tests_responses = $this->separate_test_feedback->getElementsByTagNameNS($this->namespace_feedback, "tests-response")[0];
        foreach ($tests_responses->getElementsByTagNameNS($this->namespace_feedback, "test-response") as $test_response) {
            $subtests_response = $test_response->getElementsByTagNameNS($this->namespace_feedback, 'subtests-response');
            if ($subtests_response->length == 0) {
                //No subtests
                $this->test_results[$test_response->getAttribute('id')] = $test_response->getElementsByTagNameNS($this->namespace_feedback, 'test-result')[0];
            } else {
                //Add an array of all subtests
                $subtests = [];
                foreach ($subtests_response[0]->getElementsByTagNameNS($this->namespace_feedback, 'subtest-response') as $subtest_response) {
                    $subtests[$subtest_response->getAttribute('id')] = $subtest_response->getElementsByTagNameNS($this->namespace_feedback, 'test-result')[0];
                }
                $this->test_results[$test_response->getAttribute('id')] = $subtests;
            }
        }
        //Preprocess files
        foreach ($this->feedback_files->getElementsByTagNameNS($this->namespace_feedback, "file") as $file) {
            $this->files[$file->getAttribute('id')] = $file;
        }

        $this->max_score_grading_hints = $this->calculate_max_score($this->grading_hints_root);
        if (abs($this->max_score_grading_hints - $this->max_score_lms) > 1e-5) {
            $this->score_compensation_factor = $this->max_score_lms / $this->max_score_grading_hints;
        }
    }

    public function processResult() {

        //TODO: Handle case that root element doesn't have any child elements

        $this->detailedFeedback = new separate_feedback_text_node('detailed_feedback', 'Detailed feedback');
        $this->calculatedScore = $this->calculate_from_children($this->grading_hints_root, $this->detailedFeedback);
        $this->detailedFeedback->setScore($this->calculatedScore);

        $this->summarisedFeedback = new separate_feedback_text_node('summarised_feedback', 'Summarized feedback');
        $this->fillFeedbackNodeWithFeedbackList($this->summarisedFeedback, $this->separate_test_feedback->getElementsByTagNameNS($this->namespace_feedback, 'submission-feedback-list')[0]);
    }

    public function getCalculatedScore() {
        return $this->calculatedScore;
    }

    public function getDetailedFeedback(): separate_feedback_text_node {
        return $this->detailedFeedback;
    }

    public function getSummarisedFeedback(): separate_feedback_text_node {
        return $this->summarisedFeedback;
    }

    private function calculate_from_children(\DOMElement $elem, separate_feedback_text_node $detailedFeedback, $scale_score_to_lms = true) {
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
        $detailedFeedback->setAccumulatorFunction($function);
        $counter = 0;
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'test-ref') as $testref) {
            $det_feed = new separate_feedback_text_node($detailedFeedback->getId() . '_' . $counter++);
            $detailedFeedback->addChild($det_feed);

            $score = $this->get_weighted_score_testref($testref, $det_feed, $scale_score_to_lms);
            //Execute function and only later set score to 0 because the above function also fills the feedback elements
            if ($this->should_be_nullified_elem($testref)) {
                $score = 0;
                $det_feed->setNullified(true);
            }
            $det_feed->setScore($score);

            $value = $merge_func($value, $score);
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'combine-ref') as $combineref) {
            $det_feed = new separate_feedback_text_node($detailedFeedback->getId() . '_' . $counter++);
            $detailedFeedback->addChild($det_feed);

            $score = $this->get_weighted_score_combineref($combineref, $det_feed, $scale_score_to_lms);
            //Execute function and only later set score to 0 because the above function also fills the feedback elements
            if ($this->should_be_nullified_elem($combineref)) {
                $score = 0;
                $det_feed->setNullified(true);
            }
            $det_feed->setScore($score);

            $value = $merge_func($value, $score);
        }
        return $value;
    }

    private function calculate_max_score(\DOMElement $elem) {
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
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'test-ref') as $testref) {

            $weight = 1;
            if ($testref->hasAttribute('weight')) {
                $weight = $testref->getAttribute('weight');
            }

            $value = $merge_func($value, $weight);
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'combine-ref') as $combineref) {

            $refid = $combineref->getAttribute('ref');
            $combine = $this->grading_hints_combines[$refid];
            $maxScore = $this->calculate_max_score($combine);
            $weight = 1;
            if ($combineref->hasAttribute('weight')) {
                $weight = $combineref->getAttribute('weight');
            }

            $value = $merge_func($value, $maxScore * $weight);
        }
        return $value;
    }

    private function get_weighted_score_combineref(\DOMElement $elem, separate_feedback_text_node $detailedFeedbackNode, $scale_score_to_lms = true) {
        $refid = $elem->getAttribute('ref');
        $combine = $this->grading_hints_combines[$refid];
        $score = $this->calculate_from_children($combine, $detailedFeedbackNode, $scale_score_to_lms);
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $detailedFeedbackNode->setHeading('Combined result');
        return $score * $weight;
    }

    private function get_weighted_score_testref(\DOMElement $elem, separate_feedback_text_node $detailedFeedbackNode, $scale_score_to_lms = true) {
        $refid = $elem->getAttribute('ref');
        if ($elem->hasAttribute('sub-ref')) {
            $test_result = $this->test_results[$refid][$elem->getAttribute('sub-ref')];
        } else {
            $test_result = $this->test_results[$refid];
        }
        $score = $test_result->getElementsByTagNameNS($this->namespace_feedback, 'result')[0]
                        ->getElementsByTagNameNS($this->namespace_feedback, 'score')[0]->nodeValue;
        $weight = 1;
        if ($elem->hasAttribute('weight')) {
            $weight = $elem->getAttribute('weight');
        }
        $detailedFeedbackNode->setHeading('Test ');
        $this->fillFeedbackNodeWithTestInfos($elem, $detailedFeedbackNode, $test_result);
        if ($scale_score_to_lms) {
            return $score * $weight * $this->score_compensation_factor;
        } else {
            return $score * $weight;
        }
    }

    private function fillFeedbackNodeWithTestInfos(\DOMElement $elem, separate_feedback_text_node $node, \DOMElement $testResult) {
        $refid = $elem->getAttribute('ref');
        if (($titleList = $elem->getElementsByTagNameNS($this->namespace_gradinghints, 'title'))->length == 1) {
            $node->setTitle($titleList[0]->nodeValue);
        } else {
            $node->setTitle($this->tests[$refid]->getElementsByTagNameNS($this->namespace_gradinghints, 'title')[0]->nodeValue);
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace_gradinghints, 'description'))->length == 1) {
            $node->setDescription($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS($this->namespace_gradinghints, 'description'))->length == 1) {
                $node->setDescription($list[0]->nodeValue);
            }
        }
        if (($list = $elem->getElementsByTagNameNS($this->namespace_gradinghints, 'internal-description'))->length == 1) {
            $node->setInternalDescription($list[0]->nodeValue);
        } else {
            if (($list = $this->tests[$refid]->getElementsByTagNameNS($this->namespace_gradinghints, 'internal-description'))->length == 1) {
                $node->setInternalDescription($list[0]->nodeValue);
            }
        }

        $this->fillFeedbackNodeWithFeedbackList($node, $testResult->getElementsByTagNameNS($this->namespace_feedback, 'feedback-list')[0]);
    }

    private function fillFeedbackNodeWithFeedbackList(separate_feedback_text_node $node, \DOMElement $feedback_list) {
        foreach ($feedback_list->getElementsByTagNameNS($this->namespace_feedback, 'student-feedback') as $stud_feedback) {
            $node->addStudentFeedback(
                    [
                        "title" => ($tmp = $stud_feedback->getElementsByTagNameNS($this->namespace_feedback, 'title'))->length == 1 ? $tmp[0]->nodeValue : null,
                        "content" => ($tmp = $stud_feedback->getElementsByTagNameNS($this->namespace_feedback, 'content'))->length == 1 ? $tmp[0]->nodeValue : null,
                        "files" => $this->extractFileInfosFromFilerefs($stud_feedback)
                    ]
            );
        }
        foreach ($feedback_list->getElementsByTagNameNS($this->namespace_feedback, 'teacher-feedback') as $teach_feedback) {
            $node->addTeacherFeedback(
                    [
                        "title" => ($tmp = $teach_feedback->getElementsByTagNameNS($this->namespace_feedback, 'title'))->length == 1 ? $tmp[0]->nodeValue : null,
                        "content" => ($tmp = $teach_feedback->getElementsByTagNameNS($this->namespace_feedback, 'content'))->length == 1 ? $tmp[0]->nodeValue : null,
                        "files" => $this->extractFileInfosFromFilerefs($teach_feedback)
                    ]
            );
        }
    }

    private function extractFileInfosFromFilerefs(\DOMElement $elem) {
        $embeddedFiles = [];
        $attachedFiles = [];
        if (($tmp = $elem->getElementsByTagNameNS($this->namespace_feedback, 'filerefs'))->length == 1) {
            $filerefs = $tmp[0];
            foreach ($filerefs->getElementsByTagNameNS($this->namespace_feedback, 'fileref') as $fileref) {
                $refid = $fileref->getAttribute('refid');
                $file = $this->files[$refid];

                $elem = null;
                if (($tmp = $file->getElementsByTagNameNS($this->namespace_feedback, 'embedded-bin-file'))->length == 1) {
                    $elem = $tmp[0];
                } else if (($tmp = $file->getElementsByTagNameNS($this->namespace_feedback, 'embedded-txt-file'))->length == 1) {
                    $elem = $tmp[0];
                }
                if ($elem != null) {
                    //embedded file
                    $embeddedFiles[] = ['id' => $refid, 'filename' => $elem->getAttribute('filename'), 'title' => $file->getAttribute('title')];
                    continue;
                }

                if (($tmp = $file->getElementsByTagNameNS($this->namespace_feedback, 'attached-bin-file'))->length == 1) {
                    $elem = $tmp[0];
                } else if (($tmp = $file->getElementsByTagNameNS($this->namespace_feedback, 'attached-txt-file'))->length == 1) {
                    $elem = $tmp[0];
                }
                $attachedFiles[] = ['filename' => $elem->nodeValue, 'title' => $file->getAttribute('title')];
            }
        }
        return ["embeddedFiles" => $embeddedFiles, "attachedFiles" => $attachedFiles];
    }

    //Nullifying from here on

    private function should_be_nullified_elem(\DOMElement $elem): bool {
        $nullify_condition_list = $elem->getElementsByTagNameNS($this->namespace_gradinghints, 'nullify-condition');
        if ($nullify_condition_list->length == 1) {
            $nullify_condition = $nullify_condition_list[0];
            return $this->should_be_nullified_single($nullify_condition);
        }
        $nullify_conditions_list = $elem->getElementsByTagNameNS($this->namespace_gradinghints, 'nullify-conditions');
        if ($nullify_conditions_list->length == 1) {
            $nullify_conditions = $nullify_conditions_list[0];
            return $this->should_be_nullified_composite($nullify_conditions);
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
        $combine = $this->grading_hints_combines[$refid];
        $score = $this->calculate_from_children($combine, new separate_feedback_text_node('dummy') /* This is just a dummy object */, false);
        return $score;
    }

    private function get_nullify_test_value(\DOMElement $elem): float {
        $refid = $elem->getAttribute('ref');
        if ($elem->hasAttribute('sub-ref')) {
            $test_result = $this->test_results[$refid][$elem->getAttribute('sub-ref')];
        } else {
            $test_result = $this->test_results[$refid];
        }
        $score = $test_result->getElementsByTagNameNS($this->namespace_feedback, 'result')[0]
                        ->getElementsByTagNameNS($this->namespace_feedback, 'score')[0]->nodeValue;
        return $score;
    }

    private function get_nullify_literal_value(\DOMElement $elem): float {
        return $elem->getAttribute('value');
    }

    private function should_be_nullified_composite(\DOMElement $elem): bool {
        if ($elem->getAttribute('compose-op') == 'and') {
            $value = true;
            $merge_func = function($prev, $next) {
                return $prev && $next;
            };
        } else {
            $value = false;
            $merge_func = function($prev, $next) {
                return $prev || $next;
            };
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'nullify-conditions') as $conditions) {
            $value = $merge_func($value, $this->should_be_nullified_composite($conditions));
        }
        foreach ($elem->getElementsByTagNameNS($this->namespace_gradinghints, 'nullify-condition') as $condition) {
            $value = $merge_func($value, $this->should_be_nullified_single($condition));
        }
        return $value;
    }

}
