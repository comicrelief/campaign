Feature: Landing-page
  Check a landing page and make sure that it contains the mentioned paragraphs

  @api @content
  Scenario: Landing-page /fundraise
    Given I am on "/fundraise"
    Then I should see "THE COUNTDOWN IS ON"
    And I should see "EDDIE IZ RUNNING…AGAIN"
    And I should see "Rich text bg title"
    And I should see "You’ll be helping others"
    And I should see "The money you raise will help change lives"
    And I should see "Ready to go?"
    And I should see "All that's left to do is pre-order your FREE Fundraising Pack"
    And I should see the link "Pre-order"
    And I should see "Follow in Dermont's footsteps"
    And I should see "Join in, have fun and change lives"
    And I should see the link "Get your Dancing Kit"
    And I should see the link "Get fundraising ideas"
    And I should see "Single Msg Standard 4:3"
    And I should see "Single Msg Standard 4:3 Img R"
    And I should see "Single Msg Featured 16:9"
    And I should see "Single Msg Featured 16:9 Img R"
    And I should see the link "Linko"

  @api @content
  Scenario: Check metatags for landing pages
    Given I am on "/fundraise"
    Then the metatag attribute "title" should contain the value "Fundraise"
    And the metatag property "og:title" should contain the value "Fundraise"
    And the metatag property "og:type" should have the value "article"
    And the metatag property "og:url" should contain the value "fundraise"

  @api
  Scenario: Create landing page node
    Given I am viewing a "landing" content:
      | title | Behat test landing pagee |
      | body | My freshly created body copy |
      | field_landing_copy_position_alig | Right |
      | field_landing_background_colour | White |
    Then I should see "My freshly created body copy"

  @api @functionality
  Scenario: Create landing page with associated paragraphs
    Given I am viewing a "landing" content with "Test landing page" title and "http://dummyimage.com/600x16:9" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body and with the following paragraphs:
      | type | title | image | body | variant | bg_color | featured | image_right |
      | cr_single_message_row | SMR Title | http://dummyimage.com/400x16:9 | Title above image right | title-above-image-right | | | |
      | cr_single_message_row | SMR Title 2 | http://dummyimage.com/400x16:9 | Title inside image left | title-inside-image-left | | | |
      | cr_single_message_row | SMR Title 3 | http://dummyimage.com/400x16:9 | Centre image below | centre-image-below | | | |
      | cr_single_message_row | SMR Title 4 | http://dummyimage.com/400x16:9 | Centre image above | centre-image-above | | | |
      | cr_single_message_row | SMR Title 5 | | Centred, text only, image optional | centre-text-only | | | |
      | cr_rich_text_paragraph | | http://dummyimage.com/400x16:9 | <h2>Rich text bg title</h2> <p>Rich text paragraph body </p> | | | | |
      | single_msg | Single Message 1 | http://dummyimage.com/400x16:9 | SMR 1 with cream grey background | | bg--cream-grey | 0 | 0 |
      | single_msg | Single Message 2 | http://dummyimage.com/400x16:9 | SMR 2 with white background | | bg--white | 1 | 1 |
    # And I break
    Then I should see "Behat or Liv?"
    And I should see "Rich text paragraph"
    And I should see "SMR Title"
    And I should see "Title above image right"
    And I should see "Centred, text only, image optional"

  @api @content
  Scenario: Add a partner logo
    Given I am logged in as a user with the "Administrator" role
    And I am on "/test-landing-page"
    And I click "Edit"
    And I press the "Add existing Partner" button
    And I fill in "field_paragraphs[0][subform][field_partner_list][form][entity_id]" with "Three (4)"
    And I press the "Add Partner" button
    And I press the "Save and keep published" button
    Then I should see the image "sites/default/files/partner/logo/3_49_82.gif"
    And I click "Edit"
    And I press the "edit-field-paragraphs-0-subform-field-partner-list-entities-0-actions-ief-entity-remove" button
    And I press the "Save and keep published" button
    Then I should not see the image "sites/default/files/partner/logo/3_49_82.gif"

