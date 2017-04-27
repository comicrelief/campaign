Feature: Simple-XML-Sitemap

  @api
  Scenario: Check sitemap.xml url addresses
    Given I go to "/sitemap.xml"
    And I run cron
    And I wait for 2 seconds
    Then I should see "/partners" as a sitemap url
