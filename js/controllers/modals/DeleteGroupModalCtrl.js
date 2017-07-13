(function (configStore) {
    "use strict";
    configStore.controller(
        'DeleteGroupModalCtrl',
        ['$scope', 'GroupService', '$modalInstance', 'group',
            function ($scope, GroupService, $modalInstance, group) {
                $scope.error = null;
                $scope.disableSubmit = false;
                $scope.group = group;

                $scope.deleteGroupApps = false;

                $scope.delete = function () {
                    $scope.disableSubmit = true;
                    $scope.error = null;

                    GroupService.deleteGroup($scope.group, $scope.deleteGroupApps).then(function () {
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
