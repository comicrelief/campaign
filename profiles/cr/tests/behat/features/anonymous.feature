Feature: Anonymous

  @blackbox
  Scenario: Anonymous homepage
    Given I am on the homepage
    Then I should see "What's going on"
