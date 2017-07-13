@api @patterns
Feature: Config API value transformation
  In order to get my application configuration with precise value types
  As an API client
  I should be able to use special patterns in config definition to request a certain type

  Background:
    Given the following apps:
      | name   | api_key |
      | my-app | api_key |
    And I remove all headers
    And I set header "X-APIKEY" with value "api_key"
    And I set header "accept" with value "application/json"

  Scenario Outline: Test string patterns
    Given I set the config <config_key> to <config_value> for the app <app_slug>
    And I send a GET request to "/myconfig"
    Then the response code should be 200
    And the response should contain json:
    """
      {
        "<config_key>": <expected_value>
      }
    """

    Examples:
      | app_slug | config_key   | config_value                                | expected_value                              |
      | my-app   | test_string  | my string                                   | "my string"                                 |
      | my-app   | test_string  | "my string"                                 | "my string"                                 |
      | my-app   | test_integer | #123                                        | 123                                         |
      | my-app   | test_float   | #123.24                                     | 123.24                                      |
      | my-app   | test_boolean | true                                        | true                                        |
      | my-app   | test_boolean | false                                       | false                                       |
      | my-app   | test_null    | null                                        | null                                        |
      | my-app   | test_null    | NULL                                        | null                                        |
      | my-app   | test_array   | [#14, "my_val", false]                      | [14, "my_val", false]                       |
      | my-app   | test_array   | [[#14, #56], ["my_val", another_val, true]] | [[14, 56], ["my_val", "another_val", true]] |
