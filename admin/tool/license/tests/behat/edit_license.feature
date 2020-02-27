@tool @tool_license
Feature: Custom licenses
  In order to use custom licenses
  As an admin
  I need to be able to add custom licenses

  Scenario: Add custom license
    Given I log in as "admin"
    And I navigate to "Licences > Manage licences" in site administration
    And I click on "Create license" "link"
    And I set the following fields to these values:
      | shortname      | MIT                                 |
      | fullname       | MIT License                         |
      | source         | https://opensource.org/licenses/MIT |
      | version[day]   | 1                                   |
      | version[month] | January                             |
      | version[year]  | 2020                                |
    And I press "Save changes"
    Then I should see "Manage licences"
    And I should see "MIT License" in the "MIT" "table_row"
    And I should see "https://opensource.org/licenses/MIT" in the "MIT" "table_row"
    And I log out

  Scenario: Custom license source must be a valid http(s) url including scheme.
    Given I log in as "admin"
    And I navigate to "Licences > Manage licences" in site administration
    And I click on "Create license" "link"
    And I set the following fields to these values:
      | shortname      | MIT                                 |
      | fullname       | MIT License                         |
      | source         | opensource.org/licenses/MIT         |
      | version[day]   | 1                                   |
      | version[month] | January                             |
      | version[year]  | 2020                                |
    And I press "Save changes"
    Then I should see "Invalid source URL"
    And I set the following fields to these values:
      | source         | mailto:tomdickman@catalyst-au.net   |
    And I press "Save changes"
    Then I should see "Invalid source URL"
    And I set the following fields to these values:
      | source         | https://opensource.org/licenses/MIT |
    And I press "Save changes"
    Then I should see "Manage licences"
    And I should see "MIT License" in the "MIT" "table_row"
    And I should see "https://opensource.org/licenses/MIT" in the "MIT" "table_row"
    And I log out

  Scenario: Custom license version format must be YYYYMMDD00
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
    Then I should see "Manage licences"
    And I should see "2019030100" in the "MIT" "table_row"
    And I log out

  @javascript
  Scenario: Custom license short name should not be editable
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
    And I should see "Manage licences"
    And I should see "MIT License" in the "MIT" "table_row"
    And I click on "Edit" "icon" in the "MIT" "table_row"
    Then I should see "Edit license"
    And the "shortname" "field" should be disabled
    And I log out