Feature: News-page

  Scenario: News-page /whats-going-on
    Given I am on "/whats-going-on"
    Then I should see the text "Filter category"

 @api
  Scenario: News-page /yplan-partners-sport-relief
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/8/edit"
    And I enter "YPlan partners with Comic Relief" for "title"
    And press "Save and keep published"
    And I go to "/whats-going-on/yplan-partners-sport-relief"
    Then I should see the text "The partnership will also give users the ability to buy tickets from a"
