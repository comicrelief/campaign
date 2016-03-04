Feature: Search

  @blackbox
  Scenario: Search /search
    Given I am on "search?text=Lindsey"
    Then I should see the link "Get to know Comic Relief" 
