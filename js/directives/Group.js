(function(configStore) {
    "use strict";

    configStore.directive('group', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
                slug: '@'
            },
            controller: 'GroupCtrl as groupCtrl',
            templateUrl: '/templates/Group.html'
        };
    });
})(window.config_store);
