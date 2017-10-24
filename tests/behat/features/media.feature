@local
Feature: Media
  This feature covers media (including downloadables)

  Background:
    Given I am logged in as a user with the "editor" role

  @api @javascript
  Scenario: Create a downloadable image
    When I visit "/media/add/cr_file"
    And I enter "nose (administration)" for "Media name"
    And I enter "Red Nose!" for "Display title"
    And I select "Events" from "Category"
    And I attach the local file "nose.jpg" to "File"
    And I press "Save"
    Then I should see "File nose (administration) has been created."
    And I should see the link "Red Nose!"
    When I go to "admin/content/media"
    Then I should see the link "nose (administration)"

  @api @javascript
  Scenario: Create a downloadable pdf
    When I visit "/media/add/cr_file"
    And I enter "pdf (administration)" for "Media name"
    And I enter "Sample PDF" for "Display title"
    And I select "Fundraise" from "Category"
    And I attach the local file "sample.pdf" to "File"
    And I wait for AJAX to finish
    And I press "Save"
    Then I should see "File pdf (administration) has been created."
    And I should see the link "Sample PDF"
    When I go to "admin/content/media"
    Then I should see the link "pdf (administration)"

  @api
  Scenario: Create an external file
    When I visit "/media/add/cr_external_file"
    And I enter "ext file (administration)" for "Media name"
    And I enter "http://www.pdf995.com/samples/pdf.pdf" for "URL"
    And I enter "Sample external PDF" for "Link text"
    And I select "TV" from "Resource category"
    And I press "Save"
    Then I should see "File ext file (administration) has been created."
    And I should see the link "Sample external PDF"
    When I go to "admin/content/media"
    Then I should see the link "ext file (administration)"

  @api @test
  Scenario: Create a video media item
    When I visit "/media/add/video"
    And I enter "Video (administration name)" for "Media name"
    And I enter "cBkTjkKrLqs" for "Youtube Video ID"
    And I enter "Here goes the video caption" for "Video caption"
    And I press "Save"
    And I go to "admin/content/media"
    Then I should see the link "Video (administration name)"

  @api @test @javascript
  Scenario: Create a video media file
    When I visit "/media/add/cr_file"
    And I enter "video file (administration)" for "Media name"
    And I enter "Sample video file" for "Display title"
    And I select "Event" from "Category"
    And I attach the local file "testvideo.mp4" to "File"
    And I wait for AJAX to finish
    And I press "Save"
    Then I should see "File video file (administration) has been created."
    And I should see the link "Sample video file"
