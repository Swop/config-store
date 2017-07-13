(function (configStore, _) {
    "use strict";
    configStore.controller(
        'MainCtrl',
        ['$scope',
            function ($scope) {
                $scope.showSpinner = false;
                $scope.$on('spinnerDisplay', function(event, display) {
                    $scope.showSpinner = display;
                });
            }]);
})(window.config_store);
