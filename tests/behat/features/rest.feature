Feature: REST API

  @api
  Scenario: Check main menu REST endpoint
    Given I am on "entity/menu/main/tree?_format=json"
    Then the response status code should be 200
