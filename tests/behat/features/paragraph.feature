Feature: Paragraphs
  Create Partner list, Story row, Rich text, Single msg paragraph in a landing page and verify

  Background:
    Given I am logged in as a user with the "Editor user" role

  @api @functionality @default-content @javascript
  Scenario: Create Partner list in landing page and verify
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/cr/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body
    And I click on "#block-tabs>nav>ul>li:contains('Edit')" element
    And I wait for AJAX loading to finish
    And I select "Partner list" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add Row component" button
    And I wait for AJAX loading to finish
    And I press the "Add existing Partner" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[0][subform][field_partner_list][form][entity_id]" with "Three (4)"
    And I press the "Add Partner" button
    And I wait for AJAX loading to finish
    And I press the "Save and keep published" button
    And I wait for AJAX loading to finish
    Then I should see the image "/sites/default/files/partner/logo/3_49"

  @api @functionality
  Scenario: Create story row paragraph in landing page and verify
    And I add "cr_story" paragraph with following fields in a test landing page:
      | field_cr_story_title             | Testing story row                                                      |
      | field_cr_story_fundraiser_total  | £158                                                                   |
      | field_cr_story_fundraiser_copy   | Baking competition for Red Nose Day                                    |
      | field_cr_story_fundraiser_image  | profiles/contrib/cr/tests/behat/files/400x4:3.png                      |
      | field_cr_story_fundraiser_bg_col | Yellow                                                                 |
      | field_cr_story_beneficiary_copy  | In Africa, that's enough to buy 63 mosquito nets that protect children |
      | field_cr_story_beneficiary_image | profiles/contrib/cr/tests/behat/files/400x4:3.png                      |
    When I am on "/test-landing-page"
    Then I should see "Testing story row"
    And I should see "£158"
    And I should see "baking competition for Red Nose Day"
    And I should see "In Africa, that's enough to buy 63 mosquito nets that protect children"

  @api @functionality
  Scenario: Create rich text paragraph in landing page and verify
    And I add "cr_rich_text_paragraph" paragraph with following fields in a test landing page:
      | field_background | profiles/contrib/cr/tests/behat/files/400x16:9.png           |
      | field_body       | <h2>Rich text bg title</h2> <p>Rich text paragraph body </p> |
    When I am on "/test-landing-page"
    Then I should see "Rich text paragraph"

  @api @functionality
  Scenario: Create single message paragraph in landing page and verify
    And I add "single_msg" paragraph with following fields in a test landing page:
      | field_single_msg_title | Single Message 1                                   |
      | field_single_msg_img   | profiles/contrib/cr/tests/behat/files/400x16:9.png |
      | field_single_msg_bg    | bg--gainsboro-grey                                 |
      | field_single_msg_body  | SMR 1 with cream grey background                   |
    When I am on "/test-landing-page"
    Then I should see "SMR 1 with cream grey background"

