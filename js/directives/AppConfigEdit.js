(function(configStore) {
    "use strict";

    configStore.directive('appConfigEdit', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
                'slug': '@',
                'comparedAppSlug': '@?'
            },
            controller: 'AppConfigEditCtrl as appConfigEditCtrl',
            templateUrl: '/templates/AppConfigEdit.html'
        };
    });
})(window.config_store);
