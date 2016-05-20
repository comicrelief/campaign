Feature: Create
  This feature covers anything that needs to be created via the CMS. Starting with articles and users.

  @api
  Scenario: Create users
    Given users:
    | name       | mail                   | status |
    | Joe Bloggs | joe.bloggs@example.com | 1      |
    And I am logged in as a user with the "administrator" role
    When I visit "admin/people"
    Then I should see the link "Joe Bloggs"

  @api
  Scenario: Create news-article
    Given I am logged in as a user with the "editor" role
    When I go to "node/add/article"
    And I enter "article one" for "title"
    And I enter "22/03/2016" for "edit-field-article-publish-date-0-value-date"
    And I enter "image.jpg" for "edit-field-article-image-0-upload"
    And I enter "https://youtu.be/JCUFs2qJ1bs" for "edit-field-youtube-url-0-input"
    And I enter "An amazing intro" for "edit-field-article-intro-0-value"
    And I enter "Amazing body copy" for "edit-body-0-value"
    And I enter "tag 1, tag 2, tag 3" for "edit-field-article-tags-target-id"
    And press "Save"
    When I go to "/whats-going-on/article-one"
    # Then I should see "article one"
    # Then I should see "22/03/2016"
    # Then I should see "image.jpg"
    # Then I should see "https://youtu.be/JCUFs2qJ1bs"
    # Then I should see "An amazing intro"
    # Then I should see "Amazing body copy"
