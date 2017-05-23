Feature: Homepage
  To check the header links are present and go to where they are supposed to

  @default-content
  Scenario: Header-links
    Given I am on the homepage
    Then I should see the link "Home" in the "header_nav" region
    And I should see the link "Fundraise" in the "header_nav" region
    And I should see the link "What's going on" in the "header_nav" region
    And I should see the link "FAQ" in the "footer" region
    And I should see the link "Legal" in the "footer" region
    And I should see the link "Partners" in the "footer" region

