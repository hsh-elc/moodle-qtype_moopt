<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\proforma_xml;

/**
 * Description of separate_feedback_text_node
 *
 * @author robin
 */
class separate_feedback_text_node {

    private $id;
    private $heading;
    private $children;
    private $isNullified;
    private $score;
    private $accumulatorFunction;
    private $internalDescription;
    private $title;
    private $description;
    private $studentFeedback;
    private $teacherFeedback;
    private $hasinternalerror;
    private $maxScore;

    public function __construct($id, $heading = null, $content = null) {
        $this->id = $id;
        $this->heading = $heading;
        $this->content = $content;
        $this->children = [];
        $this->studentFeedback = [];
        $this->teacherFeedback = [];
        $this->filerefs = [];
    }

    public function addChild(separate_feedback_text_node $node) {
        $this->children[] = $node;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function getId() {
        return $this->id;
    }

    public function getHeading() {
        return $this->heading;
    }

    public function setHeading($heading) {
        $this->heading = $heading;
    }

    public function setNullified($isNullified) {
        $this->isNullified = $isNullified;
    }

    public function isNullified() {
        return $this->isNullified;
    }

    public function getScore() {
        return $this->score;
    }

    public function setScore($score) {
        $this->score = $score;
    }

    public function getAccumulatorFunction() {
        return $this->accumulatorFunction;
    }

    public function setAccumulatorFunction($accumulatorFunction) {
        $this->accumulatorFunction = $accumulatorFunction;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getInternalDescription() {
        return $this->internalDescription;
    }

    public function setInternalDescription($internalDescription) {
        $this->internalDescription = $internalDescription;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getStudentFeedback() {
        return $this->studentFeedback;
    }

    public function getTeacherFeedback() {
        return $this->teacherFeedback;
    }

    public function addStudentFeedback($feedback) {
        if ($feedback['content'] == null && $feedback['title'] == null) {
            return;
        }
        $this->studentFeedback[] = $feedback;
    }

    public function addTeacherFeedback($feedback) {
        if ($feedback['content'] == null && $feedback['title'] == null) {
            return;
        }
        $this->teacherFeedback[] = $feedback;
    }

    public function hasInternalError() {
        return $this->hasinternalerror;
    }

    public function setHasInternalError($err) {
        $this->hasinternalerror = $err;
    }

    public function setMaxScore($score) {
        $this->maxScore = $score;
    }

    public function getMaxScore() {
        return $this->maxScore;
    }

}
