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
    Then I should see the link "YPlan partners with"

  @api @javascript
  Scenario: Search pdf file in DB
    #Create pdf media file
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_file"
    And I enter "rnd17_kids_snuffles-money-box" for "Media name"
    And I enter "Kids Snuffles Money Box" for "Display title"
    And I select "Kids" from "Category"
    And I attach the file "/tests/behat/files/rnd17_kids_snuffles-money-box.pdf" to "File"
    And I wait for AJAX to finish
    And I press "Save and publish"
    And I wait for 5 seconds

    #Search in the DB media pdf
    Given I am on "search?text=money+box"
    Then I should see the link "Kids Snuffles Money Box"
