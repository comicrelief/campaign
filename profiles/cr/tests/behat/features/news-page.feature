Feature: News-page
  Edit a news page and change the title. Redirect should be put in place.

  #Scenario: News-page /whats-going-on
  #  Given I am on "/whats-going-on"
  #  Then I should see the text "Filter category:"

 @api
  Scenario: News-page /yplan-partners-sport-relief
    Given I am logged in as a user with the "editor" role
    And I am on "/whats-going-on/yplan-partners-sport-relief"
    And I follow "Edit"
    Then I should see "Edit News article YPlan partners with Sport Relief"
    And I enter "YPlan partners with Comic Relief" for "edit-title-0-value"
    And press "Save"
    And I go to "/whats-going-on/yplan-partners-sport-relief"
    Then I should see the text "YPlan partners with Comic Relief"
    And I go to "/whats-going-on/yplan-partners-comic-relief"
    Then I should see the text "YPlan partners with Comic Relief"
