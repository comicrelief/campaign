Feature: News-page

  Scenario: News-page /whats-going-on
    Given I am on "whats-going-on"
    Then I should see "What's going on" in the "content" region

  @api
  Scenario: News-page /four-down-one-to-go
    Given I am logged in as a user with the "administrator" role
    And I am on "admin/config/search/redirect/add"
    And I enter "/whats-going-on/4" for "edit-redirect-source-0-path"
    And I enter "/whats-going-on/four-down-one-to-go" for "edit-redirect-redirect-0-uri"
    And press "Save"
    And I go to "/whats-going-on/4"
    Then I should see the link "here"
