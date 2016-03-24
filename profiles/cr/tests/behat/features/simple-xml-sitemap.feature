Feature: Simple-XML-Sitemap
  
  See if sitemap.xml is accessible
  Check sitemap.xml is updated when content is created and published

  @blackbox
  Scenario: Validate sitemap.xml
    Given I go to "/sitemap.xml"
    Then I should see the correct sitemap elements
      
  @api
  Scenario: Check sitemap.xml url addresses
    Given I go to "/sitemap.xml"
      Then I should see "/fundraise" as a sitemap url
      And I should see "/whats-going-on" as a sitemap url
      And I should see "/whats-going-on/british-triathlon-cheers-greg-james-on" as a sitemap url
      And I should see "/content-wall" as a sitemap url
    