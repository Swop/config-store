(function(configStore) {
    "use strict";

    configStore.directive('userList', function() {
        return {
            restrict: 'E',
            replace: 'true',
            scope: {
            },
            controller: 'UserListCtrl as userListCtrl',
            templateUrl: '/templates/UserList.html'
        };
    });
})(window.config_store);
