Feature: ESU
	Checks the various Email Sign Up blocks

  Background:
    Given I am on "/test/esu"

  @javascript @default-content
  Scenario: ESU Fundraise
    Then I should see "ESU Fundraise: initial message" in the "esu_fundraise" region
    And I fill in "edit-email--2" with "test-fundraise@example.org" in the "esu_fundraise" region
    And I press "Sign Up" in the "esu_fundraise" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Fundraise: success message" in the "esu_fundraise" region
    And I should have received the following data in the "esu" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | email | device | subscribeLists |
      | * | esu | * | * | * | test-fundraise@example.org | * | * |

  @javascript @default-content
  Scenario: ESU Standard
    Then I should see "ESU Standard: initial message" in the "esu_standard" region
    And I should not see "success" in the "esu_standard" region
    And I fill in "edit-email" with "test@example.org" in the "esu_standard" region
    And I press "Go" in the "esu_standard" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Standard: success! (first message)" in the "esu_standard" region
    And I should have received the following data in the "esu" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | email | device | subscribeLists |
      | * | esu | * | * | * | test@example.org | * | * |
    # @TODO: fix rest of this test - somehow I don't manage to click the second time?
    #And I select "HE" from "edit-school-phase" in the "esu_standard" region
    #And I press "Go" in the "esu_standard" region
    #And I wait for AJAX loading to finish
    #Then I should see "ESU Standard: success! (second message)" in the "esu_standard" region

  @javascript @default-content
  Scenario: ESU Workplace
    Then I should see "ESU Workplace: initial message" in the "esu_workplace" region
    And I fill in "edit-email--2" with "test-workplace@example.org" in the "esu_workplace" region
    And I fill in "edit-firstname" with "Test Workplace First Name" in the "esu_workplace" region
    And I press "Sign Up" in the "esu_workplace" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Workplace: success message" in the "esu_workplace" region
    And I should have received the following data in the "esu" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | firstName | email | device | subscribeLists |
      | * | WorkplaceESU | * | * | * | Test Workplace First Name | test-workplace@example.org | * | * |

  @javascript @default-content
  Scenario: ESU Register your Interest.(Should be only an email)
    Then I should see "ESU Register Interest: initial message" in the "esu_register_interest" region
    And I fill in "edit-email--3" with "test-register-interest@example.org" in the "esu_register_interest" region
    And I press "Subscribe" in the "esu_register_interest" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Register Interest: success message" in the "esu_register_interest" region
    And I should have received the following data in the "register_interest" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | email | device  |
      | * | RegisterInterest | * | * | * | test-register-interest@example.org | * |
