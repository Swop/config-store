(function(configStore) {
    "use strict";

    configStore.directive('user', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
                'slug': '@'
            },
            controller: 'UserCtrl as userCtrl',
            templateUrl: '/templates/User.html'
        };
    });
})(window.config_store);
