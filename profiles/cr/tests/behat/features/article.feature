Feature: Article
  This feature covers news articles

  @api
  Scenario: Article linking through from /whats-going
    Given I am on "whats-going-on"
    And I should see the link "Four down – one to go!"
    Then I follow "Four down – one to go!"
    And I should see the text "Greg James struggled on the penultimate day of Gregathlon"

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

  @api
  Scenario: Create news articles that are linked together via a common tag
    Given a "tags" term with the name "Fundraising"
    When I am viewing a "article" content:
    | title       | cComic Relief raises £1bn over 30-year existence |
    | field_article_publish_date | 2015-02-08 17:45:00                       |
    | field_article_intro | Since the charity was founded 30 years ago, with more than £78m raised. |
    | body | Comic Relief founder Richard Curtis said he was "enormously proud" of the charity's achievements. |
    | field_article_image | http://lorempixel.com/400/200/sports/Comic-Relief |
    | field_youtube_url | https://youtu.be/JCUFs2qJ1bs |
    | field_article_tags | Fundraising |
    Then I should see "Richard Curtis"
    And I should see "£1bn"
    And I should not see "£78m raised"
    And I am viewing a "article" content:
    | title       | cCelebrities come together for a stellar Night of TV for Sport Relief |
    | field_article_publish_date | 2015-02-08 17:45:00                       |
    | field_article_intro | Audiences across the UK are in for a night of first-class entertainment.  |
    | body | A one-off Luther special will be screened, with Idris Elba starring alongside Lenny Henry, Rio Ferdinand, Denise Lewis, Louis Smith, Ian Wright and David Haye. |
    | field_article_image | http://lorempixel.com/400/200/nature/Comic-Relief/ |
    | field_article_tags | Fundraising |
    Then I should see "Luther"
    And I should see "Keep up with all the news"
    And I should see "Comic Relief raises £1bn over 30-year existence"
    # Let's clear the caches if not our related news won't show up since we visited that page before!
    And the cache has been cleared
    And I click "Comic Relief raises £1bn over 30-year existence"
    Then I should see "Celebrities come together for a stellar Night of TV for Sport Relief"

