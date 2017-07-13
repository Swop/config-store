(function(configStore) {
    "use strict";

    configStore.directive('configItemsEdit', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
                app: '=',
                comparedApp: '=?',
                group: '=?',
                displaySearch: '=?',
                filters: '=?',
                actionBar: '=?'
            },
            controller: 'ConfigItemsEditCtrl as configItemsEditCtrl',
            templateUrl: '/templates/ConfigItemsEdit.html'
        };
    });
})(window.config_store);
