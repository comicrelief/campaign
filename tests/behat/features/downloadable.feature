Feature: Downloadable
  This feature covers downloadables

  # Adding @not-on-travis tag, tracking: RND-490
  @api @javascript @not-on-travis
  Scenario: Create a downloadable image
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_file"
    And I enter "nose (administration)" for "Media name"
    And I enter "Red Nose!" for "Display title"
    And I select "Events" from "Category"
    And I attach the file "profiles/contrib/cr/tests/behat/files/nose.jpg" to "File"
    And I wait for AJAX to finish
    And I press "Save and publish"
    Then I should see "File nose (administration) has been created."
    And I should see the link "Red Nose!"
    When I go to "admin/content/media"
    Then I should see the link "nose (administration)"

  @api @javascript @not-on-travis
  Scenario: Create a downloadable pdf
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_file"
    And I enter "pdf (administration)" for "Media name"
    And I enter "Sample PDF" for "Display title"
    And I select "Fundraise" from "Category"
    And I attach the file "profiles/contrib/cr/tests/behat/files/sample.pdf" to "File"
    And I wait for AJAX to finish
    And I press "Save and publish"
    Then I should see "File pdf (administration) has been created."
    And I should see the link "Sample PDF"
    When I go to "admin/content/media"
    Then I should see the link "pdf (administration)"

  @api @javascript
  Scenario: Create an external file
    Given I am logged in as a user with the "editor" role
    When I visit "/media/add/cr_external_file"
    And I enter "ext file (administration)" for "Media name"
    And I enter "http://www.pdf995.com/samples/pdf.pdf" for "URL"
    And I enter "Sample external PDF" for "Link text"
    And I select "TV" from "Resource category"
    And I press "Save and publish"
    Then I should see "File ext file (administration) has been created."
    And I should see the link "Sample external PDF"
    When I go to "admin/content/media"
    Then I should see the link "ext file (administration)"
