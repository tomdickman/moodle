@tool @tool_license
Feature: Delete custom licenses
  In order to manage custom licenses
  As an admin
  I need to be able to delete custom licenses but not standard Moodle licenses

  @javascript
  Scenario: I can delete a custom license
    Given I log in as "admin"
    And I navigate to "Licences > Manage licences" in site administration
    And I click on "Create license" "link"
    And I set the following fields to these values:
    | shortname      | MIT                                 |
    | fullname       | MIT License                         |
    | source         | https://opensource.org/licenses/MIT |
    | version[day]   | 1                                   |
    | version[month] | March                               |
    | version[year]  | 2019                                |
    And I press "Save changes"
    And I click on "Delete" "icon" in the "MIT" "table_row"
    And I click on "Save changes" "button" in the "Delete license" "dialogue"
    Then I should not see "MIT License" in the "manage-licenses" "table"
    And I log out

  @javascript
  Scenario: I cannot delete a standard Moodle license
    Given I log in as "admin"
    And I navigate to "Licences > Manage licences" in site administration
    Then I should see "License not specified" in the "unknown" "table_row"
    And I should not see "Delete" in the "unknown" "table_row"