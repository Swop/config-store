(function (configStore, _) {
    "use strict";
    configStore.controller(
        'AddAppModalCtrl',
        ['$scope', 'AppService', '$modalInstance', 'group', 'duplicateConfigFromApp',
            function ($scope, AppService, $modalInstance, group, duplicateConfigFromApp) {
                $scope.app = {
                    name: '',
                    description: '',
                    group: group ? group.id : ''
                };

                $scope.error = null;
                $scope.disableSubmit = false;
                $scope.duplicateConfigFromApp = duplicateConfigFromApp ? duplicateConfigFromApp : null;

                $scope.add = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    AppService.createApp($scope.app, $scope.duplicateConfigFromApp).then(
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
