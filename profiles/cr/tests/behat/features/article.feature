Feature: Article
  This feature covers news articles

  @api
  Scenario: Article linking through from /whats-going
    Given I am on "whats-going-on"
    And I should see the link "Four down – one to go!"
    Then I follow "Four down – one to go!"
    And I should see the text "Greg James struggled on the penultimate day of Gregathlon"

  @api
  Scenario: Article pagination on /whats-going
    Given I am on "whats-going-on"
    And I click "Next"
    Then I should see the link "Greg James begins his Gregathlon for Sport Relief"
    And I click "Next"
    Then I should see the link "You’re helping us win the fight against malaria"

  @api
  Scenario: News page /yplan-partners-sport-relief
    Given I am logged in as a user with the "editor" role
    And I am on "/whats-going-on/yplan-partners-sport-relief"
    And I follow "Edit"
    Then I should see "Edit News article YPlan"
    And I enter "YPlan partners with Comic Relief" for "edit-title-0-value"
    And press "Save"
    And I go to "/whats-going-on/yplan-partners-sport-relief"
    Then I should see the text "YPlan partners with Comic Relief"
    And I go to "/whats-going-on/yplan-partners-comic-relief"
    Then I should see the text "YPlan partners with Comic Relief"

  @api @test
  Scenario: Check metatags for articles
    Given I am on "whats-going-on/greg-james-begins-his-gregathlon-sport-relief"
    Then the metatag attribute "title" should contain the value "Greg James begins his Gregathlon for Sport Relief"
    And the metatag property "og:title" should contain the value "Greg James begins his Gregathlon for Sport Relief"
    And the metatag property "og:type" should have the value "article"
    And the metatag attribute "keywords" should have the value "Challenges"
    And the metatag attribute "description" should contain the value "Greg James has set off on the first of his five triathlons for BBC Radio"
    And the metatag property "og:description" should contain the value "Greg James has set off on the first of his five triathlons for BBC Radio"
    And the metatag property "og:image" should contain the value "news/2016-02/greg_james_gregathlon_belfast_and_so_it_begins"
    And the metatag property "og:url" should contain the value "whats-going-on/greg-james-begins-his-gregathlon-sport-relief"

  @api 
  Scenario: Create news articles using scheduled updates
    Given I am logged in as a user with the "editor" role
    And I am on "node/add/article"
    And I enter "Test Scheduled article" for "edit-title-0-value"
    And I press "Add new Publishing Date"
    # And I wait for AJAX loading to finish
    Then I should see "Update Date/time"
    And I enter todays date for "publishing_date[form][inline_entity_form][update_timestamp][0][value][date]"
    And I enter the time for "publishing_date[form][inline_entity_form][update_timestamp][0][value][time]"
    And I press "Create Publishing Date"
    # And I wait for AJAX loading to finish
    # And I break
    Then I should see "PUBLISHING DATE"
    And I enter "tag1" for "edit-field-article-tags-target-id"
    And press "Save as unpublished"

  @api
  Scenario: Create news articles that are linked together via a common tag
    Given a "tags" term with the name "Fundraising"
    When I am viewing a "article" content:
    | title       | Comic Relief raises £1bn over 30-year existence |
    | field_article_intro | Since the charity was founded 30 years ago, with more than £78m raised. |
    | body | Comic Relief founder Richard Curtis said he was "enormously proud" of the charity's achievements. |
    | field_article_image | http://dummyimage.com/400x4:3 |
    | field_youtube_url | https://youtu.be/JCUFs2qJ1bs |
    | field_article_tags | Fundraising |
    Then I should see "Richard Curtis"
    And I should see "£1bn"
    And I should not see "£78m raised"
    And I am viewing a "article" content:
    | title       | Celebrities come together for a stellar Night of TV for Sport Relief |
    | field_article_publish_date | 2015-02-08 17:45:00                       |
    | field_article_intro | Audiences across the UK are in for a night of first-class entertainment.  |
    | body | A one-off Luther special will be screened, with Idris Elba starring alongside Lenny Henry, Rio Ferdinand, Denise Lewis, Louis Smith, Ian Wright and David Haye. |
    | field_article_image | http://dummyimage.com/400x4:3 |
    | field_article_tags | Fundraising |
    Then I should see "Luther"
    And I should see "Keep up with all the news"
    And I should see "Comic Relief raises £1bn over 30-year existence"
    # Let's clear the caches if not our related news won't show up since we visited that page before!
    And the cache has been cleared
    And I click "Comic Relief raises £1bn over 30-year existence"
    Then I should see "Celebrities come together for a stellar Night of TV for Sport Relief"


