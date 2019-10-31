@tool @tool_license
Feature: License manager
  In order to manage licenses
  As an admin
  I need to be able to view and alter licence preferences in the license manager.

  Scenario: I should be able to see the default Moodle licences.
    Given I log in as "admin"
    When I navigate to "Licences > Manage licences" in site administration
    Then I should see "Licence not specified" in the "unknown" "table_row"
    And I should see "All rights reserved" in the "allrightsreserved" "table_row"
    And I should see "Public domain" in the "public" "table_row"
    And I should see "Creative Commons" in the "cc" "table_row"
    And I should see "Creative Commons - NoDerivs" in the "cc-nd" "table_row"
    And I should see "Creative Commons - No Commercial NoDerivs" in the "cc-nc-nd" "table_row"
    And I should see "Creative Commons - No Commercial" in the "cc-nc" "table_row"
    And I should see "Creative Commons - No Commercial ShareAlike" in the "cc-nc-sa" "table_row"
    And I should see "Creative Commons - ShareAlike" in the "cc-sa" "table_row"
    And I log out

    Scenario: I should be able to select any standard license as site default license
      Given I log in as "admin"
      And I navigate to "Licences > Manage licences" in site administration
      When I expand all fieldsets
      Then the "Default site licence" select box should contain "Licence not specified"
      And the "Default site licence" select box should contain "All rights reserved"
      And the "Default site licence" select box should contain "Public domain"
      And the "Default site licence" select box should contain "Creative Commons"
      And the "Default site licence" select box should contain "Creative Commons - NoDerivs"
      And the "Default site licence" select box should contain "Creative Commons - No Commercial NoDerivs"
      And the "Default site licence" select box should contain "Creative Commons - No Commercial"
      And the "Default site licence" select box should contain "Creative Commons - No Commercial ShareAlike"
      And the "Default site licence" select box should contain "Creative Commons - ShareAlike"

    Scenario: I should be able to set a site default license
      Given I log in as "admin"
      And I navigate to "Licences > Manage licences" in site administration
      When I set the field "Default site licence" to "Public domain"
      And I press "Save changes"
      Then the field "Default site licence" matches value "public"
      When I set the field "Default site licence" to "Creative Commons"
      And I press "Save changes"
      Then the field "Default site licence" matches value "cc"
      And I log out

    @javascript
    Scenario: I should be able to enable and disable licenses
      Given I log in as "admin"
      And I navigate to "Licences > Manage licences" in site administration
      When I set the field "Default site licence" to "Public domain"
      And I press "Save changes"
      Then "Default" "icon" should exist in the "public" "table_row"
      And "Enable" "icon" should not exist in the "public" "table_row"
      And "Default" "icon" should not exist in the "cc" "table_row"
      When I set the field "Default site licence" to "Creative Commons"
      And I press "Save changes"
      Then "Default" "icon" should exist in the "cc" "table_row"
      And "Enable" "icon" should not exist in the "cc" "table_row"
      And "Default" "icon" should not exist in the "public" "table_row"
      And I log out
