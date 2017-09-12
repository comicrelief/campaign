Feature: User
  This feature covers users and roles

  @api
  Scenario: Create an unpublished node and check this with reviewer role
    Given I am logged in as a user with the "reviewer" role
    When an unpublished "partner" content with the title "Unpublished partner page"
    Then I should get a "200" HTTP response
    And I should see "Unpublished partner page"

  @api
  Scenario: Create an unpublished node and check as logged out user
    Given an unpublished "partner" content with the title "Unpublished partner page"
    Then I should get a "403" HTTP response
    And I should not see "Unpublished partner page"

  @api
  Scenario: Navigate to user management interface and check HTTP response
    Given I am logged in as a user with the "manager" role
    When I am on "/admin/people"
    Then I should get a "200" HTTP response

  @api
  Scenario: Navigate to user management interface and check HTTP response
    Given I am logged in as a user with the "editor" role
    When I am on "/admin/people"
    Then I should get a "403" HTTP response

  @api
  Scenario: Check two different editors both have access to edit a single page
    Given I am logged in as a user with the "editor" role
    When an unpublished "page" content with the title "Unpublished basic page"
    And I click "Edit"
    Then I should get a "200" HTTP response
