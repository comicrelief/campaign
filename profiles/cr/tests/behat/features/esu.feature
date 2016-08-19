Feature: ESU
	Checks the various Email Sign Up blocks

  @javascript
  Scenario: ESU Standard
    Given I am on "/esu"
    Then I should see "ESU Standard: initial message" in the "esu_standard" region
    And I should not see "success" in the "esu_standard" region
    And I fill in "edit-email" with "test@example.org" in the "esu_standard" region
    And I press "Go" in the "esu_standard" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Standard: success! (first message)" in the "esu_standard" region
    And I should have received the following data in the "esu" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | email | device | source | lists |
      | RND17 | esu | * | * | * | test@example.org | * | Banner | * |
    # @TODO: fix rest of this test - somehow I don't manage to click the second time?
    And I select "HE" from "edit-school-phase" in the "esu_standard" region
    And I press "Go" in the "esu_standard" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Standard: success! (second message)" in the "esu_standard" region

  @javascript
  Scenario: ESU Workflow
    Given I am on "/esu"
    Then I should see "ESU Workplace: initial message" in the "esu_workflow" region
    And I fill in "edit-email--2" with "test-workflow@example.org" in the "esu_workflow" region
    And I fill in "edit-firstname" with "Test Workflow First Name" in the "esu_workflow" region
    And I press "Go" in the "esu_workflow" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Workplace: success message" in the "esu_workflow" region
    And I should have received the following data in the "esu_workplace" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | firstName | email | device | source | lists |
      | RND17 | esu | * | * | RND17_Unknown_ESU_Unknown | Test Workflow First Name | test-workflow@example.org | * | * | * |

  @javascript
  Scenario: ESU Register your Interest. Data contract is in https://docs.google.com/document/d/1zbXhQj14fZhcNDMHsyhx2JU3KSWqBT-k1jt5NkwPB_w/edit
    Given I am on "/esu"
    Then I should see "ESU Register Interest: initial message" in the "esu_register_interest" region
    And I fill in "edit-email--3" with "test-register-interest@example.org" in the "esu_register_interest" region
    And I check "EventInterest"
    And I press "Go" in the "esu_register_interest" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Register Interest: success message" in the "esu_register_interest" region
    And I should have received the following data in the "esu_register_interest" queue:
      | campaign | transType | timestamp | transSourceURL | transSource | EventInterest | firstName | email | device | source | lists |
      | RND17 | esu | * | * | RND17_Unknown_ESU_Unknown | 1 | Test Workflow First Name | test-register-interest@example.org | * | * | * |
