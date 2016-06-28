Feature: Landing-page
  Edit a news page and change the title. Redirect should be put in place.

 @api
  Scenario: Landing-page /fundraise
    Given I am logged in as a user with the "editor" role
    And I am on "/fundraise"
    Then I should see "THE COUNTDOWN IS ON"
    And I should see "EDDIE IZ RUNNING…AGAIN"
    And I should see "Rich text bg title"

# The the test above should be extended for each new paragraph type
