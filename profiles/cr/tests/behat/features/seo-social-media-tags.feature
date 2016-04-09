Feature: SEO-Social-Media-Tags

  Check site page(s) have the necessary tags for SEO and Social media platforms

  @blackbox
  Scenario: Anonymous homepage
    Given I am on the homepage
    Then the response should contain the tag "title"
    And response should contain the tag "meta" with the attribute "property" with the value "og:site_name"
    And response should contain the tag "meta" with the attribute "property" with the value "og:type"
    And response should contain the tag "meta" with the attribute "property" with the value "og:title"
    And response should contain the tag "meta" with the attribute "property" with the value "og:url"
    And response should contain the tag "link" with the attribute "rel" with the value "canonical"
    And response should contain the tag "link" with the attribute "rel" with the value "shortlink"