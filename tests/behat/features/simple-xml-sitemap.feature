Feature: Simple-XML-Sitemap

  @api
  Scenario: Check sitemap.xml url addresses
    Given I run cron
    And I go to "/sitemap.xml"
    Then I should see "/partners" as a sitemap url
