Feature: Article-link
  In order to see an article you must click a link on the news page to see the full article

  @blackbox
  Scenario: Article-link /whats-going
    Given I am on "whats-going-on"
    And I should see the link "Four down – one to go!"
    Then I follow "Four down – one to go!"
    And I should see the text "Greg James struggled on the penultimate day of Gregathlon"