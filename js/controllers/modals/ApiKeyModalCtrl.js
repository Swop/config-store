(function(configStore) {
    "use strict";
    configStore.controller(
        'ApiKeyModalCtrl',
        ['$scope', 'AppService', '$modalInstance', '$window', 'app',
            function ($scope, AppService, $modalInstance, $window, app) {
        $scope.disableSubmit = false;
        $scope.app = app;
        $scope.revokationSuccess = false;
        $scope.error = null;

        $scope.revoke = function () {
            $scope.disableSubmit = true;
            $scope.revokationSuccess = false;

                AppService.revokeApiKey($scope.app)
                .then(
                function (data) {
                    $scope.app.access_key = data.access_key;
                    $scope.disableSubmit = false;
                    $scope.revokationSuccess = true;
                }, function (errorData) {
                    $scope.error = errorData;
                    $scope.disableSubmit = false;
                }
            );
        };

        $scope.close = function () {
            $modalInstance.dismiss('close');
        };
    }]);
})(window.config_store);
