(function (configStore, _) {
    "use strict";
    configStore.controller(
        'GroupCtrl',
        ['$scope', '$window', '$modal', '$compile', 'GroupService', 'DiffService', '$rootScope',
            function ($scope, $window, $modal, $compile, GroupService, DiffService, $rootScope) {
                this.init = function () {
                    $scope.searchQuery = {
                        name: ''
                    };

                    var self = this;

                    var groupAppListMarkup = '<group-app-list ' +
                        'status="%s" ' +
                        'group="group" ' +
                        'apps="apps" ' +
                        'diff="diff" ' +
                        'panel-display-status="panelDisplayStatus" ' +
                        'search-query="searchQuery">' +
                        '</group-app-list>';

                    var noRefAppListMarkup = '<group-app-list ' +
                        'group="group" ' +
                        'apps="apps" ' +
                        'diff="diff" ' +
                        'panel-display-status="panelDisplayStatus" ' +
                        'search-query="searchQuery">' +
                        '</group-app-list>';

                    GroupService.getGroup($scope.slug).then(
                        function (group) {
                            if (group.id) {
                                var ref = group.reference;

                                if (ref) {
                                    self.initAppConfigItemsIndex([ref]);
                                }
                                _.forEach(group.apps, function (app) {
                                    if (!app.group) {
                                        app.group = group;
                                    }
                                });

                                self.initAppConfigItemsIndex(group.apps);

                                var diff = {};
                                if (ref) {
                                    _.forEach(group.apps, function (app) {
                                        diff[app.id] = DiffService.getDiff(app, ref);
                                    });
                                }

                                $scope.diff = diff;

                                $scope.group = group;
                                $scope.selectedComparedApp = $scope.group.reference;

                                $scope.panelDisplayStatus = {};
                                _.forEach($scope.group.apps, function (item) {
                                    $scope.panelDisplayStatus[item.id] = {
                                        'error': false,
                                        'warning': false
                                    };
                                });

                                if (ref) {
                                    _.forEach(['error', 'warning', 'ref', 'good'], function (status) {
                                        $compile(groupAppListMarkup.replace(/%s/g, status))($scope).appendTo(angular.element('#app-list-container'));
                                    });
                                } else {
                                    $compile(noRefAppListMarkup)($scope).appendTo(angular.element('#app-list-container'));
                                }
                            } else {
                                $scope.apps = group.apps;
                                self.initAppConfigItemsIndex($scope.apps);

                                $compile(noRefAppListMarkup)($scope).appendTo(angular.element('#app-list-container'));
                            }
                        },
                        function (error) {
                            $rootScope.$broadcast('alert', { type: 'danger', msg: error });
                        }
                    );
                };

                this.initAppConfigItemsIndex = function (apps) {
                    _.forEach(apps, function (app) {
                        var globalItemCounter = 0;
                        app.config_items = _.reduce(app.config_items, function (results, config) {
                            config.index = globalItemCounter;
                            globalItemCounter += 1;
                            results[config.key] = config;

                            return results;
                        }, {});
                    });
                };

                /*
                 * -------------- UTILS --------------
                 */

                this.getAppsCount = function () {
                    var apps = $scope.group ? $scope.group.apps : $scope.apps;

                    return apps.length;
                };

                /*
                 * -------------- MODALS --------------
                 */

                this.addConfigToAllAppsModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/AddConfigToAllAppsModal.html',
                        controller: function ($scope, $modalInstance) {
                            $scope.newConfig = {
                                key: '',
                                value: ''
                            };

                            $scope.disableSubmit = false;

                            $scope.add = function () {
                                $scope.disableSubmit = true;
                                $modalInstance.close($scope.newConfig);
                            };

                            $scope.cancel = function () {
                                $modalInstance.dismiss('cancel');
                            };
                        }
                    });

                    modalInstance.result.then(function (newConfig) {
                        var dispatchModalInstance = $modal.open({
                            templateUrl: '/templates/modals/DispatchConfigModal.html',
                            controller: 'DispatchConfigModalCtrl',
                            resolve: {
                                config: function () {
                                    return newConfig;
                                },
                                app: function () {
                                    return null;
                                },
                                groupId: function () {
                                    return $scope.group ? $scope.group.id : 'other';
                                }
                            }
                        });

                        dispatchModalInstance.result.then(function () {
                            window.location.reload();
                        });
                    });
                };

                this.addNewAppModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/AddAppModal.html',
                        controller: 'AddAppModalCtrl',
                        resolve: {
                            group: function () {
                                return $scope.group;
                            },
                            duplicateConfigFromApp: function () {
                                return undefined;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                this.editGroupModalOpen = function (group) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/EditGroupModal.html',
                        controller: 'EditGroupModalCtrl',
                        resolve: {
                            group: function () {
                                return group;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                this.deleteGroupModalOpen = function (group) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/DeleteGroupModal.html',
                        controller: 'DeleteGroupModalCtrl',
                        resolve: {
                            group: function () {
                                return group;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.href = '/';
                    });
                };
            }]);
})(window.config_store, _);
