@wip
Feature: Search

  @default-content
  Scenario: Search in the DB Lindsey
    Given I am on "search?text=Lindsey"
    Then I should see the link "Fundraise"

  @default-content
  Scenario: Search in the DB malaria
    Given I am on "search?text=malaria"
    Then I should see the link "You’re helping us win the fight against malaria"
    And I should see the link "Things heat up in The Great Sport Relief Bake Off tent"

  @default-content
  Scenario: Search in the DB Boppers
    Given I am on "search?text=Boppers"
    Then I should see the text "Sainsbury's"

  @default-content
  Scenario: Search in the DB Vitkauskas
    Given I am on "search?text=Vitkauskas"
    Then I should see the link "YPlan partners with Comic Relief"
