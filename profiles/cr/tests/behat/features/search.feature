Feature: Search

  Scenario: Search /search
    Given I am on "search?text=Lindsey"
    Then I should see the link "Fundraise"
    And I should see the link "What's going on"

  Scenario: Search /search
    Given I am on "search?text=malaria"
    Then I should see the link "You’re helping us win the fight against malaria"
    And I should see the link "Things heat up in The Great Sport Relief Bake Off tent"
