Feature: Downloadable
  This feature covers downloadables

  @api @javascript
  Scenario: Create a downloadable image
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_file"
    And I attach the file "nose.jpg" to "File"
    And I wait for AJAX to finish
    # And I break
    And I select "Events" from "Category"
    And I enter "nose (administration)" for "Media name"
    And I enter "Red Nose!" for "Display title"
    # And I break
    And I press "Save and publish"
    # And I break
    Then I should see "Red Nose!"


