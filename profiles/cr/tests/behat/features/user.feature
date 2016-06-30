Feature: Users
  This feature covers users within the CMS

  @api
  Scenario: Create users
    Given users:
    | name       | mail                   | status |
    | Joe Bloggs | joe.bloggs@example.com | 1      |
    And I am logged in as a user with the "administrator" role
    When I visit "admin/people"
    Then I should see the link "Joe Bloggs"
