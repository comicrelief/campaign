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
  Scenario: Create landing page node
    Given I am viewing a "landing" content:
    | title | Behat test landing pagee |
    | body | My freshly created body copy |
    | field_landing_copy_position_alig | Right |
    | field_landing_background_colour | White |
    Then I should see "My freshly created body copy"

  # @api @javascript
  @api
  Scenario: Create landing page with associated paragraphs
    # Given I am logged in as a user with the "editor" role
    And I am viewing a landing page with the following paragraphs:
    | type | title | body | variant |
    | cr_rich_text_paragraph | | Rich text paragraph | |
    | cr_single_message_row | SMR Title | SMR Body | title-above-image-right |
    # Then I break
    Then I should see "Rich text paragraph"
    And I should see "SMR Title"
    And I should see "SMR Body"
