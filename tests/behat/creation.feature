@qtype @qtype_moopt @javascript @_file_upload
Feature: MooPT Question Creation

  Scenario: Teacher creates a MooPT question via drag and drop
    Given MooPT is configured for testing
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the default editor is set to "textarea"
    And I am on the "Course 1" "core_question > course question bank" page logged in as "teacher1"
    When I press "Create a new question ..."
    And I click on "item_qtype_moopt" "radio"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I upload "question/type/moopt/tests/behat/fixtures/tasks/question_creation_task.xml" file to "ProFormA task file" filemanager
    And I press "Extract information"
    And I press "Save changes"
    And I am on the "Creation Test Question" "core_question > edit" page
    Then the field "Question name" matches value "Creation Test Question"
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
