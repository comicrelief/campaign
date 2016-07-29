Feature: Search

  @content
  Scenario: Search /search_db Lindsey
    Given I am on "search_db?text=Lindsey"
    Then I should see the link "Fundraise"

  @content
  Scenario: Search /search_db malaria
    Given I am on "search_db?text=malaria"
    Then I should see the link "You’re helping us win the fight against malaria"
    And I should see the link "Things heat up in The Great Sport Relief Bake Off tent"

  @content
  Scenario: Search /search_db Boppers
    Given I am on "search_db?text=Boppers"
    Then I should see the link "Sainsbury"

  @content
  Scenario: Search /search_db Vitkauskas
    Given I am on "search_db?text=Vitkauskas"
    Then I should see the link "YPlan partners with Comic Relief"
