(function (configStore) {
    "use strict";
    configStore.controller(
        'DeleteAppModalCtrl',
        ['$scope', 'AppService', '$modalInstance', 'app',
            function ($scope, AppService, $modalInstance, app) {
                $scope.error = null;
                $scope.disableSubmit = false;
                $scope.app = app;

                $scope.delete = function () {
                    $scope.disableSubmit = true;
                    $scope.error = null;

                    AppService.deleteApp($scope.app).then(function () {
                            $modalInstance.close();
                        }, function (data) {
                            $scope.error = data;
                            $scope.disableSubmit = false;
                        }
                    );
                };

                $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                };
            }]);
})(window.config_store);
