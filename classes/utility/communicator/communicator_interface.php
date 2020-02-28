<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace qtype_programmingtask\utility\communicator;

interface communicator_interface {

    public function getGraders(): array;

    public function isTaskCached($uuid): bool;

    public function enqueueSubmission(string $graderid, bool $asynch, \stored_file $submissionfile);

    public function getGradingResult(string $graderid, string $gradeprocessid);
}
