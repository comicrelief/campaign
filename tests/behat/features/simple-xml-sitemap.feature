Feature: Simple-XML-Sitemap

  @api
  Scenario: Check sitemap.xml url addresses
    Given I run cron
    When I go to "/sitemap.xml"
    Then I should see "/partners" as a sitemap url
