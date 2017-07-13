(function (configStore, _) {
    "use strict";
    configStore.controller(
        'AddGroupModalCtrl',
        ['$scope', 'GroupService', '$modalInstance',
            function ($scope, GroupService, $modalInstance) {
                $scope.group = {
                    name: ''
                };

                $scope.error = null;
                $scope.disableSubmit = false;

                $scope.add = function (isValid) {
                    if (!isValid) {
                        return;
                    }

                    $scope.disableSubmit = true;
                    $scope.error = null;
                    GroupService.createGroup($scope.group).then(
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
