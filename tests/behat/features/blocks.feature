Feature: Blocks
  Create Teaser, Quote, iframe blocks in landing page and verify

  @api @functionality @default-content @javascript @wip
  Scenario: Create landing page with associated paragraphs
    Given I am logged in as a user with the "Editor user" role
    And I am viewing a "landing" content with "Test landing page" title and "profiles/contrib/cr/tests/behat/files/600x16:9.png" image and "<h1>Behat or Liv?</h1><h2>Spot the five differences!</h2>" body and with the following paragraphs:
      | type                   | title            | image                                              | body                                                         | variant | bg_color           | featured | image_right |
      | cr_rich_text_paragraph |                  | profiles/contrib/cr/tests/behat/files/400x16:9.png | <h2>Rich text bg title</h2> <p>Rich text paragraph body </p> |         |                    |          |             |
      | single_msg             | Single Message 1 | profiles/contrib/cr/tests/behat/files/400x16:9.png | SMR 1 with cream grey background                             |         | bg--gainsboro-grey | 0        | 0           |
      | single_msg             | Single Message 2 | profiles/contrib/cr/tests/behat/files/400x16:9.png | SMR 2 with white background                                  |         | bg--white          | 1        | 1           |
    Then I should see "Behat or Liv?"
    And I should see "Rich text paragraph"
    And I should see "Single Message 1"
    And I am on "/test-landing-page"
    And I click on "#block-tabs>nav>ul>li:contains('Edit')" element
    And I wait for AJAX loading to finish
    And I select "Partner list" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add another Row component" button
    And I wait for AJAX loading to finish
    And I press the "Add existing Partner" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[3][subform][field_partner_list][form][entity_id]" with "Three (4)"
    And I press the "Add Partner" button
    And I wait for AJAX loading to finish
    And I press the "Save and keep published" button
    And I wait for AJAX loading to finish
    Then I should see the image "sites/default/files/partner/logo/3_49_82.gif"

    # Add Teaser block in landing page and verify content
    Given I am on "/test-landing-page"
    And I click on "#block-tabs>nav>ul>li:contains('Edit')" element
    And I wait for AJAX loading to finish
    And I select "Content Wall" from "field_paragraphs[add_more][add_more_select]"
    And I press the "Add another Row component" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[4][subform][field_cw_title][0][value]" with "Teaser & Quote content wall"
    And I press the "Add new Row" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][info][0][value]" with "Teaser & Quote block"
    And I select "2 Col - M + M" from "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][field_cw_view_mode]"
    And I select "Teaser" from "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][actions][bundle]"
    And I press the "Add existing Content block" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][form][entity_id]" with "The countdown is on"
    And I press the "Add Content block" button
    And I wait for AJAX loading to finish

    # Add existing Quote block in landing page and verify content
    And I select "Quote Block" from "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][actions][bundle]"
    And I press the "Add existing Content block" button
    And I wait for AJAX loading to finish
    And I fill in "field_paragraphs[4][subform][field_cw_row_reference][form][inline_entity_form][field_cw_block_reference][form][entity_id]" with "Jo's Quote"
    And I press the "Add Content block" button
    And I wait for AJAX loading to finish

    # Save and publish Teaser & Quote block
    And I press the "Save and keep published" button
    And I wait for 3 seconds
    And I am on "/test-landing-page"
    Then I should see "Teaser & Quote content wall"
    And I should see "Relive all the best bits of last Red Nose Day"

    # Delete the teaser & quote block
    Given I am on "/admin/structure/block/block-content"
    And I click "Teaser & Quote block"
    And I click "edit-delete"
    And I press the "Delete" button

  @api @functionality
  Scenario: Add Iframe embedded custom block
    Given I am logged in as a user with the "Editor user" role
    And I am on "/block/add/cr_iframe_embedded"
    And I fill in "Block description" with "Comic Adventure embed"
    And I select "Blue" from "edit-field-cr-iframe-embedded-bg"
    And I fill in "Embed link" with "https://comicadventure.rednoseday.com/index.html"
    And I press the "Save" button
    Then I should see "Iframe embedded Comic Adventure embed has been created."

    # Delete Iframe embedded custom block
    Given I am logged in as a user with the "Editor user" role
    And I am on "/admin/structure/block/block-content"
    And I click "Comic Adventure embed"
    And I click "edit-delete"
    And I press the "Delete" button
    Then I should see "The custom block Comic Adventure embed has been deleted."
