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

  @api
  Scenario: Create news-article
    Given "article" content:
    | title       |
    | article one |
    | article two |
    And I am logged in as a user with the "administrator" role
    When I go to "admin/content"
    Then I should see "article one"
    And I should see "article two"