@api
Feature: Config API conflicts
  In order to get my application configuration
  As an API client
  I should be able to be blocked (or not) when requesting the Config API if I'm missing a config key (or not)

  Background:
    Given the following groups:
      | name                 |
      | My out of sync group |
      | My in sync group     |
    Given the following apps:
      | name                   | group_id | reference | config_items                                                              | api_key                |
      | my-app-out-of-sync     | 1        | false     | {"in_both": 1, "new_config_not_in_ref": 1337}                             | myappoutofsync_key     |
      | my-app-out-of-sync-2   | 1        | false     | {"in_both": 1, "config_only_in_ref": 1337, "new_config_not_in_ref": 1337} | myappoutofsync2_key    |
      | my-app-out-of-sync-ref | 1        | true      | {"config_only_in_ref": 1337, "in_both": 1}                                | myappoutofsync_ref_key |
      | my-app-in-sync         | 2        | false     | {"same": 1}                                                               | myappinsync_key        |
      | my-app-in-sync-ref     | 2        | true      | {"same": 1}                                                               | myappinsync_ref_key    |
    Given I set header "accept" with value "application/json"

  Scenario: Gets an application configuration with missing key should return a conflict error
    And   I set header "X-APIKEY" with value "myappoutofsync_key"
    Given I send a GET request to "/myconfig"
    Then the response code should be 409
    And the response should contain json:
    """
      {
        "code": 409,
        "message": "The configuration doesn't match with the reference. Please update the config before using it in your program.",
        "errors": null
      }
    """

  Scenario: Gets an application configuration with new keys not present in REF should NOT return a conflict error
    And   I set header "X-APIKEY" with value "myappoutofsync2_key"
    Given I send a GET request to "/myconfig"
    Then the response code should be 200
    And the response should contain json:
    """
      {
        "in_both": 1,
        "config_only_in_ref": 1337,
        "new_config_not_in_ref": 1337
      }
    """
