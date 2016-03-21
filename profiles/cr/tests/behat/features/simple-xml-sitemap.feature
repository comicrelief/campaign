Feature: Simple-XML-Sitemap
  To check sitemap.xml is updated when content elements is published or unpublished
      
  #Scenario: Logging into Drupal
    #Given I go to "/user/login"
    #When I fill in "Username" with "admin"
    #And I fill in "Password" with "admin"
    #And I press "Log in"
    #Then I should see "Member for"

  #Scenario Outline: Accessing sitemap.xml
    #Given I go to "/sitemap.xml"
    #Then I should see <url>    
    #Examples:
      #| campaign.local |
      #| url |

  @blackbox
  Scenario: Viewing sitemap.xml
    Given I go to "/sitemap.xml"
    Then I should see the correct sitemap elements
