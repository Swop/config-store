(function (configStore, _) {
    "use strict";
    configStore.controller(
        'EditGroupModalCtrl',
        ['$scope', 'GroupService', '$modalInstance', 'group',
            function ($scope, GroupService, $modalInstance, group) {
                $scope.group = group;
                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.edit = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    GroupService.editGroup($scope.group).then(
                        function (group) {
                            $modalInstance.close(group);
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
