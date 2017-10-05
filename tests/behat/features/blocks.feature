Feature: Blocks
  Create Teaser, Quote, iframe blocks in landing page and verify

  Background:
    Given I am logged in as a user with the "Editor user" role

  @api @functionality
  Scenario: Add Teaser block in landing page and verify content
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/cr/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body
    And I click on ".tabs.primary>li:nth-of-type(2)>a" element
    And I wait for 2 seconds
    And I select "Content Wall" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add Row component" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_title][0][value]" with "Teaser content wall testing"
    And I press the "Add new Row" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][info][0][value]" with "Teaser testing row"
    And I select "1 Col - L" from "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_view_mode]"
    And I select "Teaser" from "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][actions][bundle]"
    And I press the "Add existing Content block" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][form][entity_id]" with "The countdown is on"
    And I press the "Add Content block" button
    And I wait for 2 seconds
    And I press the "Save" button
    And I am on "/test-landing-page"
    Then I should see "Teaser content wall testing"
    And I should see "Relive all the best bits of last Red Nose Day"
    # Delete teaser row
    Given I am on "/admin/structure/block/block-content"
    And I click "Teaser testing row"
    And I click "edit-delete"
    And I press the "Delete" button

  @api @functionality @default-content
  Scenario: Add existing Quote block in landing page and verify content
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/cr/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body
    And I click on ".tabs.primary>li:nth-of-type(2)>a" element
    And I wait for 2 seconds
    And I select "Content Wall" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add Row component" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_title][0][value]" with "Quote content wall testing"
    And I press the "Add new Row" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][info][0][value]" with "Quote testing row"
    And I select "1 Col - L" from "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_view_mode]"
    And I select "Quote Block" from "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][actions][bundle]"
    And I press the "Add existing Content block" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][form][entity_id]" with "Jo's Quote"
    And I press the "Add Content block" button
    And I wait for 2 seconds
    And I press the "Save" button
    And I am on "/test-landing-page"
    Then I should see "Jo's Top Tip"
    # Delete teaser row
    Given I am on "/admin/structure/block/block-content"
    And I click "Quote testing row"
    And I click "edit-delete"
    And I press the "Delete" button

  @api @functionality
  Scenario: Add Iframe embedded custom block
    Given I am on "/block/add/cr_iframe_embedded"
    And I fill in "Block description" with "Comic Adventure embed"
    And I select "Blue" from "edit-field-cr-iframe-embedded-bg"
    And I fill in "Embed link" with "https://comicadventure.rednoseday.com/index.html"
    And I press the "Save" button
    And I wait for 2 seconds
    Then I should see "Iframe embedded Comic Adventure embed has been created."

    # Add iframe in test landing page
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/cr/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body
    And I click on ".tabs.primary>li:nth-of-type(2)>a" element
    And I wait for 2 seconds
    And I select "Block reference" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add Row component" button
    And I wait for 2 seconds
    And I fill in "field_paragraphs[0][subform][field_content_block_reference][0][target_id]" with "Comic Adventure embed"
    And I press the "Save" button
    And I am on "/test-landing-page"

    # Delete Iframe embedded custom block
    Given I am on "/admin/structure/block/block-content"
    And I click "Comic Adventure embed"
    And I click "edit-delete"
    And I press the "Delete" button
    Then I should see "The custom block Comic Adventure embed has been deleted."
