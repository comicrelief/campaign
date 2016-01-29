Feature:Meta tags

  So content contains relevant SEO
  As an editor
  I can create content and provide metatags

  @api
  Scenario: Meta-tags are auto set
    Given I am logged in as a user with the "Content editor" role
    When I go to "/node/add/page"
    Then the response status code should be 200
    And I enter "test" for "Title"
    And I enter "When tweetle beetles fight, its called a tweetle beetle battle." for "Body"
    And I enter "Text" for "edit-metatags-und-dctermstype-item-value"
    And press "Save"
    Then I should see "Standard page test has been created"
    And the response should contain "<meta name=\"description\" content=\"When tweetle beetles fight, its called a tweetle beetle battle.\" />"
    And the response should contain "<title>test | aGov</title>"
    And the response should contain "<meta name=\"dcterms.type\" content=\"Text\" />"
    And the response should contain "<meta name=\"dcterms.title\" content=\"test\" />"

  @api
  Scenario: Meta-tags can be edited
    Given I am logged in as a user with the "Content editor" role
    When I go to "/node/add/page"
    Then the response status code should be 200
    And I enter "test" for "Title"
    And I enter "When tweetle beetles fight, its called a tweetle beetle battle." for "Body"
    And I enter "And when they battle in a puddle, its a tweetle beetle puddle battle" for "edit-metatags-und-description-value"
    And I enter "Fox in socks" for "Page title"
    And I enter "Fox in socks" for "edit-metatags-und-dctermstitle-item-value"
    And press "Save"
    Then I should see "Standard page test has been created"
    And the response should contain "<meta name=\"description\" content=\"And when they battle in a puddle, its a tweetle beetle puddle battle\" />"
    And the response should contain "<title>Fox in socks</title>"
    And the response should contain "<meta name=\"dcterms.title\" content=\"Fox in socks\" />"
