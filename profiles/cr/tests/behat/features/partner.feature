Feature: Partner
  This feature covers partners

  @api @default-content
  Scenario: Partners page is working
    Given I am on "partners"
    Then I should see the text "wonderful corporate partners"
    And I should see the link "Find out more"
    And I should see the link "Visit their site"
    Then I should see the hidden partner title "Sainsbury's"

  @api @javascript
  Scenario: Create a new partner
    Given a "partner_category" term with the name "Official"
    Given I am viewing a "partner" content:
    | title | Better |
    | body | The feel good place |
    | field_partner_category | Official |
    | field_partner_external | 1 |
    | field_partner_logo | profiles/cr/tests/behat/files/400x4:3.png |
    | field_partner_website | Link - http://www.better.co.uk |
    | field_partner_image | profiles/cr/tests/behat/files/400x4:3.png, profiles/cr/tests/behat/files/400x4:3.png, profiles/cr/tests/behat/files/400x4:3.png |
    Then the url should match "partners/better"
    And I should see "Better"
