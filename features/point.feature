Feature: Point
  Scenario: I want to purchase some points
    Given I create first account
    And 6 months ago, first account purchase 500 points
    When I purchase 1000 points for first account
    Then first account balance is 1500 points
  Scenario: As a user I want to transfer some points
    Given I create first account
    And 6 months ago, first account purchase 1000 points
    And I create second account
    When I transfer 500 points from first account to second account
    Then first account balance is 500 points
    And second account balance is 500 points
  Scenario: I want to purchase some points but I have expired points
    Given I create first account
    And 3 years ago, first account purchase 500 points
    And 6 month ago, first account purchase 500 points
    When I purchase 1000 points for first account
    Then first account balance is 1500 points
