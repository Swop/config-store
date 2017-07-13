(function (configStore, _) {
    "use strict";
    configStore.controller(
        'UserListCtrl',
        ['$scope', '$window', '$modal', 'UserService', '$rootScope',
            function ($scope, $window, $modal, UserService, $rootScope) {
                $scope.groups = [];

                $scope.init = function () {
                    UserService.getUsers().then(
                        function (data) {
                            $scope.users = data;
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
                };

                $scope.viewUser = function (user) {
                    $window.location.href = '/users/' + user.slug + '/view';
                };

                $scope.promote = function (user) {
                    UserService.promoteUser(user).then(
                        function () {
                            $window.location.reload();
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
                };

                $scope.demote = function (user) {
                    UserService.demoteUser(user).then(
                        function () {
                            $window.location.reload();
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
                };

                $scope.activate = function (user) {
                    UserService.activateUser(user).then(
                        function () {
                            $window.location.reload();
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
                };

                $scope.deactivate = function (user) {
                    UserService.deactivateUser(user).then(
                        function () {
                            $window.location.reload();
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
                };

                $scope.addNewUserModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/AddUserModal.html',
                        controller: 'AddUserModalCtrl'
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };
            }]);
})(window.config_store, _);
