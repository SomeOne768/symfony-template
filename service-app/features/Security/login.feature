# This file contains a user story for demonstration only.
# Learn how to get started with Behat and BDD on Behat's website:
# http://behat.org/en/latest/quick_start.html

Feature:
    As a user
    I want to be able to register

    Scenario: I can create a user
        When I go to "/register"
        Then the response status code should be 200
        And I should see "post" in the attribute "method" of the element "form"
        And I should see "email" in the attribute "type" of the element "#login_email"
        And I should see "text" in the attribute "type" of the element "#login_plainPassword"
        And I should see "submit" in the attribute "type" of the element "button"

    Scenario: When I create a user a ihave a valid return
        When I make POST XmlHttp request to "/register" with payload
            """
            login_email=test@test.test
            login_plainPassword=test
            login_submit=1
            login__token=csrf-token
            """
        Then the response status code should be 200
