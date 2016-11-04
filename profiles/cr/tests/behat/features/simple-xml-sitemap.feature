Feature: Simple-XML-Sitemap

  @api
  Scenario: Check sitemap.xml url addresses
    Given I go to "/sitemap.xml"
    Then I run cron
    Then I wait for 2 seconds
    Then I should see "/fundraise" as a sitemap url
