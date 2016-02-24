Feature: Create
  This feature covers anything that needs to be created via the CMS. Starting with articles and users.

  @api
  Scenario: Create users
    Given users:
    | name      | mail                   | status |
    | Matt Wagg | m.wagg@comicrelief.com | 1      |
    And I am logged in as a user with the "administrator" role
    When I visit "admin/people"
    Then I should see the link "Matt Wagg"