Feature: Article
  This feature covers news articles

 @api
  Scenario: News page /yplan-partners-sport-relief
    Given I am logged in as a user with the "editor" role
    And I am on "/whats-going-on/yplan-partners-sport-relief"
    And I follow "Edit"
    Then I should see "Edit News article YPlan"
    And I enter "YPlan partners with Comic Relief" for "edit-title-0-value"
    And press "Save"
    And I go to "/whats-going-on/yplan-partners-sport-relief"
    Then I should see the text "YPlan partners with Comic Relief"
    And I go to "/whats-going-on/yplan-partners-comic-relief"
    Then I should see the text "YPlan partners with Comic Relief"

  @api
  Scenario: Create news article
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
