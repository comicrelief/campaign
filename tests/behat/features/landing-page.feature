Feature: Landing-page
  Check a landing page and make sure that it contains the mentioned paragraphs

  @api @default-content @exclude
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
      | title                            | Behat test landing pagee     |
      | body                             | My freshly created body copy |
      | field_landing_copy_position_alig | Right                        |
      | field_landing_background_colour  | White                        |
    Then I should see "My freshly created body copy"
