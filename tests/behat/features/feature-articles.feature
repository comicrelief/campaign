Feature: feature-articles
  Check the feature article block

  @api @default-content @feature-articles @exclude
  Scenario: Check that the feature articles block is rendered and displaying articles
    Given I am on "featured-stories"
    Then I should see the link "Extra article 2"
    And I should see the link "Four down – one to go!"
    And I should see the link "Things heat up in The Great Sport Relief Bake Off tent"
