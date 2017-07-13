(function (configStore, _) {
    "use strict";
    configStore.controller(
        'AddUserModalCtrl',
        ['$scope', 'UserService', '$modalInstance',
            function ($scope, UserService, $modalInstance) {
                $scope.user = {
                    name: '',
                    username: '',
                    email: ''
                };

                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.add = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    UserService.createUser($scope.user).then(
                        function (user) {
                            $modalInstance.close(user);
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
