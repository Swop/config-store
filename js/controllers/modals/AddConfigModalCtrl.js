(function(configStore) {
    "use strict";
    configStore.controller('AddConfigModalCtrl', ['$scope', 'GroupService', '$modalInstance', 'checkExistingConfigKey', function ($scope, GroupService, $modalInstance, checkExistingConfigKey) {
        $scope.newConfig = {
            key: '',
            value: ''
        };

        $scope.disableSubmit = false;
        $scope.checkExistingConfigKey = checkExistingConfigKey;

        $scope.add = function (isValid) {
            if (!isValid) {
                return;
            }

            $scope.disableSubmit = true;
            $modalInstance.close($scope.newConfig);
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }]);
})(window.config_store);
