Feature: Homepage
  To check the header links are present and go to where they are supposed to

  @default-content
  Scenario: Header-links
    Given I am on the homepage
    Then I should see the link "What's going on"
    And I should see the link "FAQ"
    And I should see the link "Legal"
    And I should see the link "Partners"
    And I should see the link "Fundraise"
