@rest
Feature: REST API

  Scenario: Check main menu REST endpoint
    When I do a GET request to "/entity/menu/main/tree"
    Then I should find in the position 1 of the menu the "title" with the value "Fundraise (Landing)"
    Then I want to find the text "FAQ" in the JSON
