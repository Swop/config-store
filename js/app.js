(function (angular, window) {
    "use strict";
    window.config_store =
        angular
            .module('ConfigStore', ['ui.utils', 'ui.bootstrap'])
            .config(function ($httpProvider) {
                $httpProvider.interceptors.push('myHttpInterceptor');
            })
            .factory('myHttpInterceptor', function ($q, $rootScope) {
                return {
                    'request': function(config) {
                        $rootScope.$broadcast('spinnerDisplay', true);
                        return config;
                    },
                    'requestError': function(rejection) {
                        $rootScope.$broadcast('spinnerDisplay', false);
                        return $q.reject(rejection);
                    },
                    'response': function(response) {
                        $rootScope.$broadcast('spinnerDisplay', false);
                        return response;
                    },
                    'responseError': function(rejection) {
                        $rootScope.$broadcast('spinnerDisplay', false);
                        return $q.reject(rejection);
                    }
                };
            });
})(angular, window);
