@qtype @qtype_moopt @javascript
Feature: MooPT Question Copying

  Background:
    Given MooPT is configured for testing
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | name            | reference | contextlevel |
      | MooPT Questions | C1        | Course       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the default editor is set to "textarea"

  Scenario: Teacher copies a MooPT Question
    Given the following "questions" exist:
      | qtype | name                   | questioncategory | template         |
      | moopt | Creation Test Question | MooPT Questions  | creation_task    |
    And I am on the "Course 1" "core_question > course question bank" page logged in as "teacher1"
    When I choose "Duplicate" action for "Creation Test Question" in the question bank
    Then the field "Question name" matches value "Creation Test Question (copy)"
    And the field "Question text" matches value "<h3>Creation Test Question</h3>Description"
    And the field "Internal description" matches value "Internal-Description"
    And the field "Task-UUID" matches value "11111111-2222-3333-4444-555555555555"
    And the field "Default mark" matches value "7"
    And the field "Enable file submissions" matches value "1"
    And the field "Enable free text submissions" matches value "1"
    And the field "Initial number of free text input fields" matches value "2"
    And the field "id_namesettingsforfreetextinput0_0" matches value "1"
    And the field "freetextinputfieldname0" matches value "FirstFile.java"
    And the field "freetextinputfieldtemplate0" matches value "template0"
    And the field "ftsinitialdisplayrows0" matches value "13"
    And the field "ftsoverwrittenlang0" matches value "java"
    And the field "id_namesettingsforfreetextinput1_1" matches value "1"
    And the field "freetextinputfieldtemplate1" matches value "template1"
    And the field "ftsinitialdisplayrows1" matches value "17"
    And the field "ftsoverwrittenlang1" matches value "txt"

  @_file_upload
  Scenario Outline: Teacher grades a copied question (file upload)
    Given the following "questions" exist:
      | qtype | name                | questioncategory | template            |
      | moopt | <question_template> | MooPT Questions  | <question_template> |
    And I am on the "Course 1" "core_question > course question bank" page logged in as "teacher1"
    When I choose "Duplicate" action for "<question_template>" in the question bank
    # The following two steps are used instead of a single 'I press "Save changes"' because this step seems to find the "Save changes and continue editing" button anyway. Probably because it comes first in the form.
    And I press "Save changes and continue editing"
    And I press "Cancel"
    And I choose "Preview" action for "<question_template> (copy)" in the question bank
    # Normal preview via "Submit and finish" seems broken for file submissions so use the immediatemoopt check button instead
    And I set the following fields to these values:
      | behaviour | immediatefeedback |
    And I press "Save preview options and start again"
    And I upload "<solution>" as MooPT submission
    And I press "Check"
    And I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    Then I should see "Mark <score> out of <maxscore>"

    Examples:
      | question_template | solution                                                                                  | score | maxscore |
      | graja_pointrotate | question/type/moopt/tests/behat/fixtures/solutions/graja_pointrotate/correct/Turn.java    | 2.00  | 2.00     |
      | graja_pointrotate | question/type/moopt/tests/behat/fixtures/solutions/graja_pointrotate/wrong/Turn.java      | 0.00  | 2.00     |
      | asqlg_quicktest   | question/type/moopt/tests/behat/fixtures/solutions/asqlg_quicktest/correct/submission.sql | 5.00  | 5.00     |
      | asqlg_quicktest   | question/type/moopt/tests/behat/fixtures/solutions/asqlg_quicktest/wrong/submission.sql   | 0.00  | 5.00     |

  Scenario Outline: Teacher grades a copied question (typed into textarea)
    Given the following "questions" exist:
      | qtype | name                | questioncategory | template            |
      | moopt | <question_template> | MooPT Questions  | <question_template> |
    And I am on the "Course 1" "core_question > course question bank" page logged in as "teacher1"
    When I choose "Duplicate" action for "<question_template>" in the question bank
    # The following two steps are used instead of a single 'I press "Save changes"' because this step seems to find the "Save changes and continue editing" button anyway. Probably because it comes first in the form.
    And I press "Save changes and continue editing"
    And I press "Cancel"
    And I disable UI plugins in the MooPT question type
    And I choose "Preview" action for "<question_template> (copy)" in the question bank
    And I set the field with xpath "//textarea[contains(@name, 'answertext0')]" to the contents of "<solution>"
    And I press "Submit and finish"
    And I should see "Your submission has been queued for automatic grading"
    And I click on "Reload" "button"
    Then I should see "Mark <score> out of <maxscore>"

    Examples:
      | question_template        | solution                                                                                    | score | maxscore |
      | graflap_grammar_for_nfa1 | question/type/moopt/tests/behat/fixtures/solutions/graflap_grammar_for_nfa1/correct/grammar | 1.00  | 1.00     |
      | graflap_grammar_for_nfa1 | question/type/moopt/tests/behat/fixtures/solutions/graflap_grammar_for_nfa1/wrong/grammar   | 0.00  | 1.00     |
      | dummygrader_quicktest    | question/type/moopt/tests/behat/fixtures/solutions/dummygrader_quicktest/correct/score      | 1.00  | 1.00     |
      | dummygrader_quicktest    | question/type/moopt/tests/behat/fixtures/solutions/dummygrader_quicktest/wrong/score        | 0.00  | 1.00     |