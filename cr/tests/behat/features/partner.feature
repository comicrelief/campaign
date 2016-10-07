Feature: Partner
  This feature covers partners

  @api @default-content
  Scenario: Partners page is working
    Given I am on "partners"
    Then I should see the text "wonderful corporate partners"
    And I should see the link "Three"
    And I should see the link "Vodafone"
    And I should see the link "PayPal"
    And I should see the link "Persil"

  @api @javascript
  Scenario: Create a new partner
    Given a "partner_category" term with the name "Official"
    Given I am viewing a "partner" content:
    | title | Better |
    | body | The feel good place |
    | field_partner_category | Official |
    | field_partner_external | 1 |
    | field_partner_logo | http://dummyimage.com/400x4:3 |
    | field_partner_website | Link - http://www.better.co.uk |
    | field_partner_image | http://dummyimage.com/400x4:3, http://dummyimage.com/400x4:3, http://dummyimage.com/400x4:3 |
    Then the url should match "partners/better"
    And I should see "Better"
