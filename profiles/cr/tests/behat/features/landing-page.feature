Feature: Landing-page
  Check a landing page and make sure that it contains the mentioned paragraphs

  @api
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

  @api
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

  @api
  Scenario: Create landing page with associated paragraphs
    Given I am viewing a "landing" content with "Test landing page" title and "http://dummyimage.com/600x16:9" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body and with the following paragraphs:
    | type | title | image | body | variant |
    | cr_single_message_row | SMR Title | http://dummyimage.com/400x16:9 | Title above image right | title-above-image-right |
    | cr_single_message_row | SMR Title 2 | http://dummyimage.com/400x16:9 | Title inside image left | title-inside-image-left |
    | cr_single_message_row | SMR Title 3 | http://dummyimage.com/400x16:9 | Centre image below | centre-image-below |
    | cr_single_message_row | SMR Title 4 | http://dummyimage.com/400x16:9 | Centre image above | centre-image-above |
    | cr_single_message_row | SMR Title 5 | | Centred, text only, image optional | centre-text-only |
    | cr_rich_text_paragraph | | http://dummyimage.com/400x16:9 | <h2>Rich text bg title</h2> <p>Rich text paragraph body </p> | |
    Then I should see "Behat or Liv?"
    And I should see "Rich text paragraph"
    And I should see "SMR Title"
    And I should see "Title above image right"
    And I should see "Centred, text only, image optional"
