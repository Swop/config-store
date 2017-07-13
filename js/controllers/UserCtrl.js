(function (configStore, _) {
    "use strict";
    configStore.controller(
        'UserCtrl',
        ['$scope', '$window', '$modal', 'UserService', '$rootScope',
            function ($scope, $window, $modal, UserService, $rootScope) {
                $scope.groups = [];

                $scope.init = function () {
                    UserService.getUser($scope.slug).then(
                        function (data) {
                            $scope.user = data;
                        },
                        function (data) {
                            var errorMessage;
                            if (angular.isObject(data)) {
                                errorMessage = data.error;
                            } else {
                                errorMessage = data;
                            }

                            $rootScope.$broadcast('alert', { type: 'danger', msg: errorMessage });
                        }
                    );

                    UserService.getMe().then(
                        function (user) {
                            $scope.currentUser = user;
                        },
                        function (error) {
                            var errorMessage;
                            if (angular.isObject(error)) {
                                errorMessage = error.error;
                            } else {
                                errorMessage = error;
                            }

                            $rootScope.$broadcast('alert', { type: 'danger', msg: errorMessage });
                        }
                    );
                };

                $scope.changePasswordModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/ChangePasswordModal.html',
                        controller: 'ChangePasswordModalCtrl',
                        resolve: {
                            user: function () {
                                return $scope.user;
                            },
                            currentUser: function () {
                                return $scope.currentUser;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                $scope.editUserModalOpen = function (user) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/EditUserModal.html',
                        controller: 'EditUserModalCtrl',
                        resolve: {
                            user: function () {
                                return user;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };
            }]);
})(window.config_store, _);
