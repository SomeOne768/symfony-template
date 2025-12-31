Feature:
  As a user
  I want to be able to see both english or french

  Scenario: I can see french translation
    Given I add the request header "Accept-Language" with the value "fr"
    And I am on "/"
    Then I should see "Accueil"

  Scenario: I can see english translation
    Given I add the request header "Accept-Language" with the value "en"
    And I am on "/"
    Then I should see "Home"
