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
    And I attach the local file "rnd17_kids_snuffles-money-box.pdf" to "File"
    And I press "Save"
    And I wait for 2 seconds
    #Search in the DB media pdf
    Given I am on "search?text=money+box"
    Then I should see the link "Kids Snuffles Money Box"

  @api
  Scenario: Search for downloadable external file
    #Create an external file
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_external_file"
    And I enter "Youth fundraising pack" for "Media name"
    And I enter "http://assets.2017.rednoseday.com.s3.amazonaws.com/Downloadables/rnd17_youth_fundraising-pack_all.pdf" for "URL"
    And I enter "Download" for "Link text"
    And I select "Schools" from "Resource category"
    And I press "Save"
    And I wait for 2 seconds
    #Search for external file
    Given I am on "search?text=Youth+fundraising+pack"
    Then I should see the link "Youth fundraising pack"

  @api
  Scenario: Search for video
    #Create video media item
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/video"
    And I enter "Media video" for "Media name"
    And I enter "PqXOlfwlVag" for "Youtube Video ID"
    And I enter "Stephen Hawking video" for "Video caption"
    And I press "Save"
    And I wait for 2 seconds
    #Search for video item
    Given I am on "search?text=Stephen+Hawking"
    Then I should see "Stephen Hawking video"
