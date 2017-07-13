(function (configStore, _) {
    "use strict";
    configStore.controller(
        'GroupAppListCtrl',
        [
            '$scope',
            '$window',
            '$filter',
            '$modal',
            'GroupService',
            'ConfigPreviewService',
            '$rootScope',
            '$compile',
            '$timeout',
            function ($scope,
                      $window,
                      $filter,
                      $modal,
                      GroupService,
                      ConfigPreviewService,
                      $rootScope,
                      $compile,
                      $timeout
            ) {

                this.init = function () {
                    $scope.consideredApps = $scope.group ? $scope.group.apps : $scope.apps;
                    $scope.filteredApps = $filter('filterByAppStatus')(
                        $scope.consideredApps,
                        $scope.group,
                        $scope.diff,
                        $scope.status
                    );

                    this.selectComparedApp($scope.group && $scope.group.reference ? $scope.group.reference : null);
                };

                /*
                 * -------------- ACTIONS --------------
                 */

                this.selectComparedApp = function (comparedApp) {
                    $scope.selectedComparedApp = comparedApp;
                };

                this.considerAsRef = function (app) {
                    GroupService.updateRef($scope.group.id, app).then(
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

                this.dropRefStatus = function () {
                    GroupService.dropRefStatus($scope.group.id).then(
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

                /*
                 * -------------- MODALS --------------
                 */

                this.duplicateAppModalOpen = function (duplicateConfigFromApp) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/AddAppModal.html',
                        controller: 'AddAppModalCtrl',
                        resolve: {
                            group: function () {
                                return $scope.group;
                            },
                            duplicateConfigFromApp: function () {
                                return duplicateConfigFromApp;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                this.editAppModalOpen = function (app, group) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/EditAppModal.html',
                        controller: 'EditAppModalCtrl',
                        resolve: {
                            app: function () {
                                return app;
                            },
                            group: function () {
                                return app.group ? app.group : group;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                this.deleteAppModalOpen = function (app) {
                    var modalInstance = $modal.open({
                        templateUrl: 'deleteApp.html',
                        controller: 'DeleteAppModalCtrl',
                        resolve: {
                            app: function () {
                                return app;
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        $window.location.reload();
                    });
                };

                this.openPreviewModal = function (app) {
                    ConfigPreviewService.openPreviewModal(app);
                };

                this.apiKeyModalOpen = function (app) {
                    $modal.open({
                        templateUrl: 'apiKey.html',
                        controller: 'ApiKeyModalCtrl',
                        resolve: {
                            app: function () {
                                return app;
                            }
                        }
                    });
                };

                /*
                 * -------------- APP PANEL MANAGEMENT --------------
                 */

                this.toggleAppPanel = function (app, type) {
                    if (this.isPanelOpened(app.id, type)) {
                        this.hideAppPanel(app.id, type);
                    } else {
                        this.showAppPanel(app, type);
                    }
                };

                this.showAppPanel = function (app, type) {
                    var configItemEditInErrorMarkup = '<config-items-edit ' +
                        'app="app" ' +
                        'compared-app="selectedComparedApp" ' +
                        'group="group" ' +
                        'display-search="false" ' +
                        'filters="{query: \'\', states: [\'missing_left\']}" ' +
                        'action-bar="light" />';

                    var configItemEditInWarningMarkup = '<config-items-edit ' +
                        'app="app" ' +
                        'compared-app="groupAppListCtrl.getRef()" ' +
                        'group="group" ' +
                        'display-search="false" ' +
                        'filters="{query: \'\', states: [\'missing_right\']}" ' +
                        'action-bar="light" />';

                    var self = this;

                    var appDisplayConfig;
                    _.forIn($scope.panelDisplayStatus, function (displayConfig, otherAppId) {
                        if (otherAppId != app.id) {
                            self.hideAppPanel(otherAppId, 'error');
                            self.hideAppPanel(otherAppId, 'warning');
                        } else {
                            appDisplayConfig = displayConfig;
                        }
                    });

                    _.forEach(_.keys($scope.panelDisplayStatus[app.id]), function (appPannelType) {
                        self.hideAppPanel(app.id, appPannelType);
                    });

                    $timeout(function(){
                        var markup = 'error' === type ? configItemEditInErrorMarkup : configItemEditInWarningMarkup;
                        var target = angular.element('#app-panel-'+type+'-config-'+app.id);
                        var subScope = $scope.$new(false, target.scope());
                        subScope.app = app;
                        //var subScope = _.assign({}, $scope, {'app': app});
                        //var subScope = $scope;
                        var subNode = $compile(markup)(subScope);
                        subNode.appendTo(target);

                        appDisplayConfig[type] = true;

                        if (typeof appDisplayConfig.scopes === 'undefined') {
                            appDisplayConfig.scopes = {};
                        }

                        appDisplayConfig.scopes[type] = subScope;
                    }, 0, false);
                };

                this.isPanelOpened = function (appId, type) {
                    return $scope.panelDisplayStatus[appId][type];
                };

                this.hideAppPanel = function (appId, type) {
                    var target = angular.element('#app-panel-'+type+'-config-'+appId);
                    //target.scope().$destoy();
                    var subScopes = $scope.panelDisplayStatus[appId].scopes;
                    if (typeof subScopes !== 'undefined' && typeof subScopes[type] !== 'undefined') {
                        subScopes[type].$destroy();
                        delete subScopes[type];
                    }
                    target.empty();

                    $scope.panelDisplayStatus[appId][type] = false;
                };

                /*
                 * -------------- UTILS --------------
                 */

                this.isRef = function (app) {
                    if ((typeof $scope.group) === "undefined") {
                        return false;
                    }

                    return ($scope.group.reference && $scope.group.reference.id === app.id);
                };

                this.getRef = function () {
                    if (this.hasRef()) {
                        return $scope.group.reference;
                    }

                    return null;
                };

                this.hasRef = function () {
                    return $scope.group && $scope.group.reference;
                };

                this.getMissing = function (app) {
                    if (!this.hasRef()) {
                        return null;
                    }
                    return this.getDiff(app, 'missing_left');
                };

                this.getNew = function (app) {
                    if (!this.hasRef()) {
                        return null;
                    }
                    return this.getDiff(app, 'missing_right');
                };

                this.getDiff = function (app, diffKey) {
                    return $scope.diff[app.id][diffKey];
                };
            }]);
})(window.config_store, _);
