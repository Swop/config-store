(function (configStore, _) {
    "use strict";
    configStore.controller(
        'ChangePasswordModalCtrl',
        ['$scope', 'UserService', '$modalInstance', 'user', 'currentUser',
            function ($scope, UserService, $modalInstance, user, currentUser) {
                $scope.user = user;
                $scope.currentUser = currentUser;
                $scope.oldPassword = '';
                $scope.newPassword = '';

                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.change = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;

                    UserService.changePassword($scope.user, $scope.newPassword, $scope.oldPassword).then(
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
