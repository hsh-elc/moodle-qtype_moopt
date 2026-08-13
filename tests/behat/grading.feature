@qtype @qtype_moopt @javascript
Feature: MooPT Question Grading
  Background:
    Given MooPT is configured for testing
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | name            | reference | contextlevel |
      | MooPT Questions | C1        | Course       |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name | course | idnumber |
      | quiz     | Quiz | C1     | quiz     |

  @_file_upload
  Scenario Outline: Student submits a solution via quiz (file upload)
    Given the following "questions" exist:
      | qtype | name                | questioncategory | template            |
      | moopt | <question_template> | MooPT Questions  | <question_template> |
    And quiz "Quiz" contains the following questions:
      | question            | page |
      | <question_template> | 1    |
    When I am on the "Quiz" "mod_quiz > View" page logged in as student1
    And I press "Attempt quiz"
    And I upload "<solution>" as MooPT submission
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    Then I should see "Mark <score> out of <maxscore>"

    Examples:
      | question_template | solution                                                                           | score | maxscore |
      | graja_pointrotate | question/type/moopt/tests/behat/fixtures/solutions/graja_pointrotate/Turn.java     | 2.00  | 2.00     |
      | asqlg_quicktest   | question/type/moopt/tests/behat/fixtures/solutions/asqlg_quicktest/submission.sql  | 5.00  | 5.00     |

  Scenario Outline: Student submits a solution via quiz (typed into textarea)
    Given the following "questions" exist:
      | qtype | name                | questioncategory | template            |
      | moopt | <question_template> | MooPT Questions  | <question_template> |
    And quiz "Quiz" contains the following questions:
      | question            | page |
      | <question_template> | 1    |
    When I am on the "Quiz" "mod_quiz > View" page logged in as student1
    And I disable UI plugins in the MooPT question type
    And I press "Attempt quiz"
    And I set the field with xpath "//textarea[contains(@name, 'answertext0')]" to the contents of "<solution>"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    Then I should see "Mark <score> out of <maxscore>"

    Examples:
      | question_template        | solution                                                                            | score | maxscore |
      | graflap_grammar_for_nfa1 | question/type/moopt/tests/behat/fixtures/solutions/graflap_grammar_for_nfa1/grammar | 1.00  | 1.00     |
      | dummygrader_quicktest    | question/type/moopt/tests/behat/fixtures/solutions/dummygrader_quicktest/score      | 1.00  | 1.00     |