(function (configStore, _) {
    "use strict";
    configStore.controller(
        'GroupListCtrl',
        ['$scope', '$window', '$modal', 'GroupService', 'DiffService', '$rootScope',
            function ($scope, $window, $modal, GroupService, DiffService, $rootScope) {
                $scope.groups = [];

                $scope.init = function () {
                    GroupService.getGroups().then(
                        function (data) {
                            $scope.groups = data.groups;
                            _.forEach($scope.groups, function (group) {
                                _.forEach(group.apps, function (app) {
                                    if (!app.group) {
                                        app.group = group;
                                    }
                                });
                            });
                            $scope.standaloneApps = data.standaloneApps;
                            $scope.status = DiffService.getGroupsDiffStatus($scope.groups);
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

                $scope.getStatusClass = function (group) {
                    var groupStatusClass = 'group-neutral';

                    if ($scope.status[group.id] && $scope.status[group.id].ref == 1) {
                        if ($scope.status[group.id].missingCounter > 0) {
                            groupStatusClass = 'group-error';
                        } else if ($scope.status[group.id].newCounter > 0) {
                            groupStatusClass = 'group-warning';
                        } else {
                            groupStatusClass = 'group-good';
                        }
                    }

                    return groupStatusClass;
                };

                $scope.addGroupModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/AddGroupModal.html',
                        controller: 'AddGroupModalCtrl'
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };
            }]);
})(window.config_store, _);
