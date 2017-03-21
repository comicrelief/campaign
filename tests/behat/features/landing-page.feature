Feature: Landing-page
  Check a landing page and make sure that it contains the mentioned paragraphs

  @api @default-content
  Scenario: Landing-page /fundraise
    Given I am on "/fundraise"
    Then I should see "Bake a massive difference"
    And I should see "Cakes are tremendous"
    And I should see "So, what's your thing?"
    And I should see "Follow in Dermont's footsteps"
    And I should see "Join in, have fun and change lives"
    And I should see "Cakier"
    And I should see the link "Get your Dancing Kit"
    And I should see the link "Get fundraising ideas"
    And I should see "Relive all the best bits of last Red Nose Day"
    And I should see "Jo's Top Tip"
    And I should see "Top-up your fundraising total with a classic, pay-to-play"
    And I should see "Get tips and tools to help you fundraise"
    And I should see "Fern Britton speaks out"
    And I should see "British Triathlon cheers Greg James on"
    And I should see "Rich text bg title"

  @api @default-content
  Scenario: Check metatags for landing pages
    Given I am on "/fundraise"
    Then the metatag attribute "title" should contain the value "Fundraise"
    And the metatag property "og:title" should contain the value "Fundraise"
    And the metatag property "og:type" should have the value "article"
    And the metatag property "og:url" should contain the value "fundraise"

  @api @default-content @javascript
  Scenario: Landing-page /video
    Given I am on "/video"
    Then I should see "Nice video background"
    And I should see a "mp4" with the following filename "VideoHeader"

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
    Given I am logged in as a user with the "Editor user" role
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/drupal/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body and with the following paragraphs:
      | type | title | image | body | variant | bg_color | featured | image_right |
      | cr_rich_text_paragraph | | profiles/contrib/drupal/tests/behat/files/400x16:9.png | <h2>Rich text bg title</h2> <p>Rich text paragraph body </p> | | | | |
      | single_msg | Single Message 1 | profiles/contrib/drupal/tests/behat/files/400x16:9.png | SMR 1 with cream grey background | | bg--gainsboro-grey | 0 | 0 |
      | single_msg | Single Message 2 | profiles/contrib/drupal/tests/behat/files/400x16:9.png | SMR 2 with white background | | bg--white | 1 | 1 |
    # And I break
    Then I should see "Behat or Liv?"
    And I should see "Rich text paragraph"
    And I should see "Single Message 1"
    And I am on "/test-landing-page"
    And I click "Edit"
    And I select "Partner list" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add another Row component" button
    And I press the "Add existing Partner" button
    And I fill in "field_paragraphs[3][subform][field_partner_list][form][entity_id]" with "Three (4)"
    And I press the "Add Partner" button
    And I press the "Save and keep published" button
    Then I should see the image "sites/default/files/partner/logo/3_49_82.gif"
