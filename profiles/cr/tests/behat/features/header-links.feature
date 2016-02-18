Feature: Header-links
  To check the header links are present and go to where they are supposed to

  @blackbox
  Scenario: Header-links /whats-going-on
    Given I am on "whats-going-on"
    And I should see the link "CR"
    Then I follow "CR"
    Then I am on "homepage"

  Scenario: Header-links homepage
    Given I am on the homepage
    And I should see the link "What's going on"
    Then I follow "What's going on"
    Then I am on "whats-going-on"