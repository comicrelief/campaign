@rest
Feature: REST API

  Scenario: Check main menu REST endpoint
    When I send a GET request to "/entity/menu/main/tree" with '{"query":{"_format":"json"}}'
    Then the response should be in JSON format
    And I should see "Fundraise (Landing)" somewhere in the response
