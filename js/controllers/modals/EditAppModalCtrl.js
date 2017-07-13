(function (configStore, _) {
    "use strict";
    configStore.controller(
        'EditAppModalCtrl',
        ['$scope', 'AppService', '$modalInstance', 'app', 'group',
            function ($scope, AppService, $modalInstance, app, group) {
                $scope.app = app;
                $scope.app.group = group;
                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.edit = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    AppService.editApp($scope.app).then(
                        function (app) {
                            $modalInstance.close(app);
                        },
                        function (error) {
                            if (angular.isObject(error)) {
                                $scope.error = error;
                            } else {
                                $scope.error.error = error;
                            }

                            $scope.disableSubmit = false;
                        }
                    );
                };

                $scope.cancel = function () {
                    $modalInstance.dismiss('cancel');
                };
            }]);
})(window.config_store, _);
