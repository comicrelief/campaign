@rest
Feature: REST API

  @api
  Scenario: Check main menu REST endpoint
    Given I am on "/entity/menu/main/tree?_format=json"
    Then I should see "Legal"
    And I should see "FAQ"
