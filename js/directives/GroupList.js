(function(configStore) {
    "use strict";

    configStore.directive('groupList', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
            },
            controller: 'GroupListCtrl as groupListCtrl',
            templateUrl: '/templates/GroupList.html'
        };
    });
})(window.config_store);
