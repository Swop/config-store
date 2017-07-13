(function(configStore, _) {
    "use strict";
    configStore.controller('DispatchConfigModalCtrl', ['$scope', 'GroupService', '$modalInstance', 'config', 'app', 'groupId', function ($scope, GroupService, $modalInstance, config, app, groupId) {
        $scope.disableSubmit = false;
        $scope.config = config;
        $scope.app = app;
        $scope.groupId = groupId;

        $scope.otherApps = [];
        $scope.configValues = {};

        $scope.dispatch = function () {
            $scope.disableSubmit = true;

            var filteredConfigValues = _.reduce($scope.configValues, function (cary, item) {
                if (item.dispatch) {
                    cary[item.app.slug] = item.value;
                }

                return cary;
            }, {});

            GroupService.dispatchConfig($scope.groupId, $scope.config.key, filteredConfigValues)
                .then(
                function (data) {
                    $modalInstance.close();
                }, function (errorData) {
                    $scope.error = errorData;
                    $scope.disableSubmit = false;
                }
            );
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };

        $scope.init = function() {
            GroupService.getGroup($scope.groupId).then(
                function (group) {
                    if ($scope.app) {
                        $scope.otherApps = _.filter(group.apps, function (item) {
                            return item.id != $scope.app.id;
                        });
                    } else {
                        $scope.otherApps = group.apps;
                    }

                    _.forEach($scope.otherApps, function (app) {
                        $scope.configValues[app.slug] = {
                            app: app,
                            dispatch: true,
                            value: $scope.config.value,
                            originalValue: null
                        };
                    });

                    GroupService.getConcurrentConfigValues($scope.groupId, $scope.config.key).then(
                        function (concurrentConfigValues) {
                            _.forEach(concurrentConfigValues, function (concurrentConfig) {
                                if ($scope.configValues.hasOwnProperty(concurrentConfig.app.slug)) {
                                    $scope.configValues[concurrentConfig.app.slug].originalValue = concurrentConfig.value;
                                    $scope.configValues[concurrentConfig.app.slug].dispatch = false;
                                }
                            });
                        }
                    );
                }
            );
        };
    }]);
})(window.config_store, _);
