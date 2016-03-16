Feature: Search

  Scenario: Search /search
    #Given I am on "search?text=Lindsey"
    #Then I should see the link "Get to know Comic Relief"

    Scenario: Search /search
      Given I am on "search?text=malaria"
      Then I should see the link "You’re helping us win the fight against malaria"
