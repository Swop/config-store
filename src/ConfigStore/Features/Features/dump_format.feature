@api @formats
Feature: Config API response format
  In order to get my application configuration with the wanted type format
  As an API client
  I should be able to request the configuration with required format

  Background:
    Given the following apps:
      | name   | config_items                           | api_key |
      | my-app | {"string": "abcde", "integer": "#123"} | api_key |
    And   I set header "X-APIKEY" with value "api_key"

  @json
  Scenario: A dump configuration call should return a valid JSON representation of the config
    Given I set header "accept" with value "application/json"
    And I send a GET request to "/myconfig"
    Then the response code should be 200
    And the response should contain json:
    """
      {
        "string": "abcde",
        "integer": 123
      }
    """

  @json
  Scenario: Require JSON format with custom root node
    Given I set header "accept" with value "application/json"
    And I send a GET request to "/myconfig?dump_options[rootNode]=custom_rootnode"
    Then the response code should be 200
    And the response should contain json:
    """
      {
        "custom_rootnode":
        {
          "string": "abcde",
          "integer": 123
        }
      }
    """

  @yaml
  Scenario: A dump configuration call should return a valid YAML representation of the config
    Given I set header "accept" with value "text/yaml"
    And I send a GET request to "/myconfig"
    Then the response code should be 200
    And the response should contain yaml:
"""
parameters:
    string: abcde
    integer: 123
"""

  @yaml
  Scenario: Require YAML format with custom root node
    Given I set header "accept" with value "text/yaml"
    And I send a GET request to "/myconfig?dump_options[rootNode]=custom_rootnode"
    Then the response code should be 200
    And the response should contain yaml:
"""
custom_rootnode:
  string: abcde
  integer: 123
"""

  @php
  Scenario: A dump configuration call should return a valid serialized PHP representation of the config
    Given I set header "accept" with value "text/x-php"
    And I send a GET request to "/myconfig"
    Then the response code should be 200
    And the imported PHP script should match with the following JSON object:
"""
{
  "integer": 123,
  "string": "abcde"
}
"""

  @php
  Scenario: Require PHP format with "define" statements
    Given I set header "accept" with value "text/x-php"
    And I send a GET request to "/myconfig?dump_options[useDefineStatements]=true"
    Then the response code should be 200
    And the imported PHP script should match with the following JSON object:
"""
{
  "integer": 123,
  "string": "abcde"
}
"""
    And the following constants must be defined:
      | name    | value | type    |
      | string  | abcde | string  |
      | integer | 123   | integer |
