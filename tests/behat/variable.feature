@qtype @qtype_moopt @javascript @_file_upload @grader_specific_asqlg
Feature: Variable Asqlg Questions
  Scenario: Student submits a correct solution for a variant of an asqlg question based on the provided template (file upload)
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
    And the following "questions" exist:
      | qtype | name           | questioncategory | template       |
      | moopt | asqlg_variable | MooPT Questions  | asqlg_variable |
    And quiz "Quiz" contains the following questions:
      | question       | page |
      | asqlg_variable | 1    |
    When I am on the "Quiz" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I upload "question/type/moopt/tests/behat/fixtures/solutions/asqlg_variable/empty/submission.sql" as MooPT submission
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    And I press "Summarized feedback"
    And I generate a solution for the subtask "DBS1.7.1.b" from the template "template_" and the solution file "question/type/moopt/tests/behat/fixtures/solutions/asqlg_variable/correct/solutions.sql"
    And I am on the "Quiz" "mod_quiz > View" page
    And I press "Re-attempt quiz"
    And I upload the generated MooPT solution
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    Then I should see "Mark 7.00 out of 7.00"
