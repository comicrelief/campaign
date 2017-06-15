Feature: Article
  This feature covers news articles

  @api @default-content
  Scenario: Article linking through from /whats-going-on
    Given I am on "whats-going-on"
    And I should see the link "Four down – one to go!"
    Then I follow "Four down – one to go!"
    And I should see the text "Greg James struggled on the penultimate day of Gregathlon"

  @api @default-content
  Scenario: Article pagination on /whats-going-on
    Given I am on "whats-going-on"
    And I click "››"
    And I wait for 3 seconds
    Then I should see the link "British Triathlon cheers Greg James on"
    And I click "‹‹"
    And I wait for 3 seconds
    Then I should see the link "Greg James begins his Gregathlon for Sport Relief"

  @api @default-content
  Scenario: News page /yplan-partners-sport-relief
    Given I am logged in as a user with the "editor" role
    And I am on "/press-release/yplan-partners-sport-relief"
    And I follow "Edit"
    And I enter "YPlan partners with Comic Relief" for "edit-title-0-value"
    And press "Save"
    And I go to "/press-release/yplan-partners-sport-relief"
    Then I should see the text "YPlan partners with Comic Relief"
    And I go to "/press-release/yplan-partners-comic-relief"
    Then I should see the text "YPlan partners with Comic Relief"

  @api @default-content
  Scenario: Check metatags for articles
    Given I am on "press-release/greg-james-begins-his-gregathlon-sport-relief"
    Then the metatag attribute "title" should contain the value "Greg James begins his Gregathlon for Sport Relief"
    And the metatag property "og:title" should contain the value "Greg James begins his Gregathlon for Sport Relief"
    And the metatag property "og:type" should have the value "article"
    And the metatag attribute "keywords" should have the value "Fundraising"
    And the metatag attribute "description" should contain the value "Greg James has set off on the first of his five triathlons for BBC Radio"
    And the metatag property "og:description" should contain the value "Greg James has set off on the first of his five triathlons for BBC Radio"
    And the metatag property "og:image" should contain the value "news/2016-02/greg_james_gregathlon_belfast_and_so_it_begins"
    And the metatag property "og:url" should contain the value "press-release/greg-james-begins-his-gregathlon-sport-relief"

  @api @javascript
  Scenario: Create news articles using scheduled updates
    Given I am logged in as a user with the "editor" role
    And I am on "node/add/article"
    And I enter "Test Scheduled article" for "edit-title-0-value"
    And I select "News" for "edit-field-article-type"
    And I press "Add new Publishing Date"
    And I wait for AJAX loading to finish
    Then I should see "Update Date/time"
    And I enter today date for "publishing_date[form][inline_entity_form][update_timestamp][0][value][date]"
    And I enter the time for "publishing_date[form][inline_entity_form][update_timestamp][0][value][time]"
    And I press "Create Publishing Date"
    And I wait for AJAX loading to finish
    Then I should see "Publishing date"
    And I enter "tag1" for "field_article_category[target_id]"
    And I scroll ".unpublish input" into view
    And press "Save as unpublished"
    # check the content cannot be seen if logged out
    Given I am not logged in
    And I am on "news-tv-and-events/news"
    Then I should not see "Test Scheduled article"
    # wait till content should be published then log back in
    And I wait for 30 seconds
    And I am logged in as a user with the "administrator" role
    # run cron and clear caches
    And am on "admin/config/system/cron"
    And press "Run cron"
    And I wait for AJAX loading to finish
    Then I should see "Cron ran successfully."
    And the cache has been cleared
    # logout and see the article loaded
    Given I am not logged in
    And I am on "news/test-scheduled-article"
    Then I should see "Test Scheduled article"

  @api
  Scenario: Create news articles that are linked together via a common tag
    Given a "category" term with the name "Fundraising"
    When I am viewing a "article" content:
      | title                  | Comic Relief raises £1bn over 30-year existence                                                   |
      | field_article_type     | News                                                                                              |
      | field_article_intro    | Since the charity was founded 30 years ago, with more than £78m raised.                           |
      | body                   | Comic Relief founder Richard Curtis said he was "enormously proud" of the charity's achievements. |
      | field_article_image    | profiles/contrib/cr/tests/behat/files/400x4:3.png                                                 |
      | field_youtube_url      | https://youtu.be/JCUFs2qJ1bs                                                                      |
      | field_article_category | Fundraising                                                                                       |
    Then I should see "Richard Curtis"
    And I should see "£1bn"
    And I should not see "£78m raised"
    And I am viewing a "article" content:
      | title                      | Celebrities come together for a stellar Night of TV for Sport Relief                                                                                            |
      | field_article_type         | News                                                                                                                                                                |
      | field_article_publish_date | 2015-02-08 17:45:00                                                                                                                                             |
      | field_article_intro        | Audiences across the UK are in for a night of first-class entertainment.                                                                                        |
      | body                       | A one-off Luther special will be screened, with Idris Elba starring alongside Lenny Henry, Rio Ferdinand, Denise Lewis, Louis Smith, Ian Wright and David Haye. |
      | field_article_image        | profiles/contrib/cr/tests/behat/files/400x4:3.png                                                                                                               |
      | field_article_category     | Fundraising                                                                                                                                                     |
    Then I should see "Luther"
    And I should see "Related news"
    And I should see "Comic Relief raises £1bn over 30-year existence"
    # Let's clear the caches if not our related news won't show up since we visited that page before!
    And the cache has been cleared
    And I click "Comic Relief raises £1bn over 30-year existence"
    Then I should see "Celebrities come together for a stellar Night of TV for Sport Relief"

  @api @default-content @exclude-articles-from-aggregator
  Scenario: Create news articles that is excluded from feature articles views
    # Create an article that is excluded from view.
    When I am viewing a "article" content:
      | title                       | Test excluded article                  |
      | field_article_intro         | This is a test of the article content. |
      | body                        | Test, Test, Test.                      |
      | field_article_exclude_aggr  | 1                                      |
      | field_article_category      | Fundraising                            |
    # Check the content is excluded from the news and events page
    Given I am not logged in
    And I am on "featured-stories"
    Then I should not see "Test excluded article"
