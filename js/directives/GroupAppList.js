(function(configStore) {
    "use strict";

    configStore.directive('groupAppList', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
                status: '@',
                group: '=',
                apps: '=',
                diff: '=',
                searchQuery: '=?',
                panelDisplayStatus: '='
            },
            controller: 'GroupAppListCtrl as groupAppListCtrl',
            templateUrl: '/templates/GroupAppList.html'
        };
    });
})(window.config_store);
