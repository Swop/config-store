(function(configStore) {
    "use strict";
    configStore.service('SecurityTokenService', ['$http', '$q', function($http, $q) {
        var securityTokens = {};
        var locks = {};

        return({
            getSecurityToken: getSecurityToken
        });

        function getSecurityToken(namespace, intention) {
            if (locks.hasOwnProperty(namespace)) {
                return locks[namespace];
            }

            locks[namespace] = $q(function(resolve, reject) {
                if (!securityTokens.hasOwnProperty(namespace)) {
                    var request = $http({
                        method: 'GET',
                        url: '/security-tokens/' + namespace
                    });

                    return request.then(handleSuccess, handleError).then(
                        function (data) {
                            securityTokens[namespace] = data;
                            delete locks[namespace];

                            resolve(extractSecurityToken(securityTokens[namespace], intention));
                        },
                        function (error) {
                            reject(error);
                        }
                    );
                } else {
                    resolve(extractSecurityToken(securityTokens[namespace], intention));
                }
            });

            return locks[namespace];
        }

        function extractSecurityToken(tokens, intention) {
            if ((typeof tokens[intention]) === "undefined") {
                return '';
            }

            return tokens[intention];
        }

        function handleError(response) {
            if (!angular.isObject(response.data)) {
                return($q.reject( "An unknown error occurred."));
            }

            return $q.reject(response.data);

        }

        function handleSuccess(response) {
            return response.data;
        }
    }]);
})(window.config_store);
