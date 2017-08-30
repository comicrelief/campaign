Feature: Homepage
  Verify header, body, footer and meta icons

  Background:
    Given I am on the homepage

  @default-content
  Scenario: Verify header region
    Then I should see the link "Home" in the "header" region
    And I should see the link "Fundraise" in the "header" region
    And I should see the link "What's going on" in the "header" region
    And I should see the link "FAQ" in the "header" region
    And I should see the link "Legal" in the "header" region
    And I should see the link "Search" in the "header" region
    And I should see the link "Log in to your Giving Page" in the "header" region
    And I should see the link "Sign up for emails" in the "header" region

  @default-content
  Scenario: Verify body region
    Then I should see the image "/sites/default/files/styles/bg_rich_text_wide/public/2016-08/desktop_header_lenny"
    And I should see "Bake a massive difference"

  @default-content
  Scenario: Verify footer region
    Then I should see the link "FAQ" in the "footer" region
    And I should see the link "Legal" in the "footer" region
    And I should see the link "Partners" in the "footer" region
    And I should see the link "Facebook" in the "footer_social" region
    And I should see the link "Twitter" in the "footer_social" region
    And I should see the link "YouTube" in the "footer_social" region
    And I should see the link "Instagram" in the "footer_social" region
    And I should see the link "Google Plus" in the "footer_social" region

  @default-content @javascript
  Scenario: Verify meta-icon tool tips
    When I hover over the element ".meta-icons__magnify"
    Then I should see "Search"
    When I hover over the element ".meta-icons__login"
    Then I should see "Log in to your Giving Page"
    When I hover over the element ".meta-icons__esu-toggle"
    Then I should see "Sign up for emails"

