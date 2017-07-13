(function (configStore, _) {
    "use strict";
    configStore.controller(
        'EditUserModalCtrl',
        ['$scope', 'UserService', '$modalInstance', 'user',
            function ($scope, UserService, $modalInstance, user) {
                $scope.user = user;

                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.edit = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    UserService.updateUser($scope.user).then(
                        function () {
                            $modalInstance.close();
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
