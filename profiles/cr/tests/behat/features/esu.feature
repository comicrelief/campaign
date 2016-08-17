Feature: ESU
	Checks the various Email Sign Up blocks 

  @javascript @content 
  Scenario: ESU Standard
    Given I am on "/esu"
    Then I should see "ESU Standard: initial message" in the "esu_standard" region
    And I fill in "edit-email" with "test@example.org"
    And I press "Go" in the "esu_standard" region
    And I wait for AJAX loading to finish
    Then I should see "ESU Standard: success! (first message)" in the "esu_standard" region
    # And I select "HE" from "school_phase"
    # And I press "Go" in the "esu_standard" region
    # And I wait for AJAX loading to finish
    # Then I should see "ESU Standard: success! (second message)" in the "esu_standard" region