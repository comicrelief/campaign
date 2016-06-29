Feature: Landing-page
  Check a landing page and make sure that it contains the mentioned paragraphs

 @api
  Scenario: Landing-page /fundraise
    Given I am logged in as a user with the "editor" role
    And I am on "/fundraise"
    Then I should see "THE COUNTDOWN IS ON"
    And I should see "EDDIE IZ RUNNING…AGAIN"
    And I should see "Rich text bg title"
    And I should see "You’ll be helping others"
    And I should see "The money you raise will help change lives"
    And I should see "Ready to go?"
    And I should see "All that's left to do is pre-order your FREE Fundraising Pack"
    And I should see the link "Pre-order"

# The the test above should be extended for each new paragraph type

  @api
  Scenario: Create landing page node
    Given I am logged in as a user with the "editor" role
    When I go to "node/add/landing"
    And I enter "Behat test landing page" for "Title"
    And I enter "My freshly created body copy" for "edit-body-0-value"
    And I select "Right" from "Body copy position"
    And I select "White" from "Background colour"
    And I press "Save"
    When I go to "/behat-test-landing-page"
    Then I should see "My freshly created body copy"
