(function (configStore, _) {
    "use strict";
    configStore.controller(
        'AlertsCtrl',
        ['$scope', '$rootScope', '$timeout',
            function ($scope, $rootScope, $timeout) {
                $scope.idCounter = 0;

                $scope.alerts = {};

                $scope.addAlert = function(alert) {
                    alert.id = $scope.idCounter;
                    $scope.idCounter += 1;

                    $scope.alerts[alert.id] = alert;

                    $timeout(function () {
                        $scope.closeAlert(alert.id);
                    }, 5000);
                };

                $scope.closeAlert = function(id) {
                    delete $scope.alerts[id];
                };

                $scope.$on('alert', function(event, alert) {
                    $scope.addAlert(alert);
                });
            }]);
})(window.config_store);
