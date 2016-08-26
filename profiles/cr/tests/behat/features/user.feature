Feature: User
  This feature covers users and roles

  @api
  Scenario: Create an unpublished node and check this with reviewer role
    Given I am logged in as a user with the "reviewer" role
    Given an unpublished "partner" content with the title "Unpublished partner page"
    Then I should see "Unpublished partner page"
    And I should not see "You are not authorized to access this page"

  @api
  Scenario: Create an unpublished node and check as logged out user
    Given an unpublished "partner" content with the title "Unpublished partner page"
    Then I should not see "Unpublished partner page"
    And I should see "You are not authorized to access this page"
