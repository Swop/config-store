(function (configStore, _, $) {
    "use strict";
    configStore.controller(
        'ConfigItemsEditCtrl',
        ['$scope', '$window', '$modal', 'AppService', '$rootScope',
            function ($scope, $window, $modal, AppService, $rootScope) {
                $scope.globalItemCounter = 0;
                $scope.states = {};
                $scope.viewType = 'edit';

                var self = this;

                this.copyRightToLeft = function (key) {
                    var leftConf = this.getConfig($scope.application.config_items, key);
                    var rightConfig = this.getConfig($scope.otherApplication.config_items, key);

                    if (typeof leftConf == "undefined") {
                        leftConf = _.clone(rightConfig, true);
                        $scope.application.config_items[leftConf.key] = leftConf;
                        $scope.application.config_items[leftConf.key].index = $scope.globalItemCounter;
                        $scope.globalItemCounter += 1;
                    } else {
                        leftConf.value = rightConfig.value;
                    }

                    this.updateStates();
                };

                this.delete = function (key) {
                    //$scope.application.config_items = _.reject($scope.application.config_items, {'key': key});
                    delete $scope.application.config_items[key];

                    this.updateStates();
                };

                this.addConfigModalOpen = function () {
                    var modalInstance = $modal.open({
                        templateUrl: 'addConfig.html',
                        controller: 'AddConfigModalCtrl',
                        resolve: {
                            checkExistingConfigKey: function () {
                                return self.checkExistingConfigKey;
                            }
                        }
                    });

                    modalInstance.result.then(function (newConfig) {
                        self.addConfig(newConfig);
                    });
                };

                this.dispatchConfigModalOpen = function (config) {
                    var modalInstance = $modal.open({
                        templateUrl: '/templates/modals/DispatchConfigModal.html',
                        controller: 'DispatchConfigModalCtrl',
                        resolve: {
                            config: function () {
                                return config;
                            },
                            app: function () {
                                return $scope.application;
                            },
                            groupId: function () {
                                return $scope.group ? $scope.group.id : 'other';
                            }
                        }
                    });

                    modalInstance.result.then(function () {
                        window.location.reload();
                    });
                };

                this.openHelpTutorial = function () {
                    $modal.open({
                        templateUrl: '/templates/modals/HelpModal.html',
                        controller: function ($scope, $modalInstance) {
                            $scope.close = function () {
                                $modalInstance.dismiss('close');
                            };
                        }
                    });
                };

                this.checkExistingConfigKey = function (key) {
                    return !_.contains(_.keys($scope.application.config_items), key);
                };

                this.addConfig = function (newConfig) {
                    var key = newConfig.key;
                    var value = newConfig.value;

                    $scope.application.config_items[key] = {
                        'key': key,
                        'value': value,
                        'index': $scope.globalItemCounter
                    };
                    $scope.globalItemCounter += 1;

                    this.updateStates();
                };

                this.updateStates = function () {
                    var statesKeys;

                    if (typeof $scope.otherApplication != 'undefined') {
                        statesKeys = _.union(_.keys($scope.application.config_items), _.keys($scope.otherApplication.config_items));
                    } else {
                        statesKeys = _.keys($scope.application.config_items);
                    }

                    $scope.states = {};

                    _.each(statesKeys, function (key) {
                        if ($scope.viewType == 'edit') {
                            $scope.states[key] = 'edit';
                            return;
                        }

                        if ($scope.application.config_items.hasOwnProperty(key) && !$scope.otherApplication.config_items.hasOwnProperty(key)) {
                            $scope.states[key] = 'missing_right';
                        } else if (!$scope.application.config_items.hasOwnProperty(key) && $scope.otherApplication.config_items.hasOwnProperty(key)) {
                            $scope.states[key] = 'missing_left';
                        } else {
                            if ($scope.application.config_items[key].value === $scope.otherApplication.config_items[key].value) {
                                $scope.states[key] = 'identical';
                            } else {
                                $scope.states[key] = 'different';
                            }
                        }
                    });
                };

                this.saveConfig = function (callback) {
                    AppService.editApp($scope.application)
                        .then(function (data) {
                            if (typeof callback === 'undefined') {
                                window.location.reload();
                            } else {
                                callback();
                            }
                        }, function (data) {
                            var errorMessage;
                            if (angular.isObject(data)) {
                                errorMessage = data.error;
                            } else {
                                errorMessage = data;
                            }

                            $rootScope.$broadcast('alert', { type: 'danger', msg: errorMessage });
                        });
                };

                this.getBackLinkUrl = function () {
                    if ($scope.application.group) {
                        return '/groups/' + $scope.application.group.id + '/apps';
                    } else {
                        return '/groups/other/apps';
                    }
                };

                this.saveConfigAndClose = function () {
                    this.saveConfig(function () {
                        window.location.href = self.getBackLinkUrl();
                    });
                };

                this.getConfig = function (configItems, key) {
                    return _.find(configItems, {'key': key});
                };

                $scope.getConfig = this.getConfig;

                this.init = function () {
                    $scope.application = $scope.app;

                    if (!$scope.group && $scope.application.group) {
                        $scope.group = $scope.application.group;
                    }

                    if ($scope.group && !$scope.application.group) {
                        $scope.application.group = $scope.group;
                    }

                    $scope.application.config_items = _.reduce($scope.application.config_items, function (results, config) {
                        config.index = $scope.globalItemCounter;
                        $scope.globalItemCounter += 1;
                        results[config.key] = config;

                        return results;
                    }, {});

                    $scope.changeComparedApp($scope.comparedApp);

                    if (typeof $scope.displaySearch === 'undefined') {
                        $scope.displaySearch = false;
                    }

                    if (typeof $scope.filters === 'undefined') {
                        $scope.filters = {
                            query: "",
                            states: []
                        };
                    }

                    if (typeof $scope.actionBar === 'undefined') {
                        $scope.actionBar = 'light';
                    }

                    this.updateStates();

                    $scope.$watch("comparedApp", function (value) {
                        $scope.changeComparedApp(value);
                    });

                    var origOffsetY;
                    var searchBar;

                    function scroll() {
                        if ($($window).scrollTop() >= origOffsetY) {
                            searchBar.addClass('sticky');
                            $('.config-items-edit-container').addClass('config-items-edit-search-padding');
                        } else {
                            searchBar.removeClass('sticky');
                            $('.config-items-edit-container').removeClass('config-items-edit-search-padding');
                        }
                    }

                    if ($scope.displaySearch) {
                        searchBar = $('.config-items-edit-search');
                        origOffsetY = searchBar.offset().top;

                        document.onscroll = scroll;
                    }
                };

                $scope.changeComparedApp = function (comparedApp) {
                    var viewType = 'edit';
                    $scope.comparedApp = comparedApp;

                    if (typeof $scope.comparedApp != 'undefined' && $scope.comparedApp !== null) {
                        $scope.otherApplication = $scope.comparedApp;

                        $scope.otherApplication.config_items = _.reduce($scope.otherApplication.config_items, function (results, config) {
                            results[config.key] = config;
                            return results;
                        }, {});

                        viewType = 'diff';
                    }

                    $scope.viewType = viewType;

                    self.updateStates();
                };

                this.objectSize = function (obj) {
                    var size = 0, key;
                    for (key in obj) {
                        if (obj.hasOwnProperty(key)) size++;
                    }
                    return size;
                };
            }]);

    configStore.controller('DiffEntryCtrl', ['$scope', function ($scope) {
        // I determine if the current line item should be
        // hidden from view due to the current search filter.
        $scope.isExcludedByFilter = applySearchFilter($scope.filters);

        // Any time the search filter changes, we may have to
        // alter the visual exlcusion of our line item.
        $scope.$watch(
            "filters.query",
            function (newName, oldName) {
                if (newName === oldName) {
                    return;
                }
                applySearchFilter($scope.filters);
            }
        );

        function applySearchFilter(filters) {
            var filter = filters.query.toLowerCase();
            var config;

            var watchedFields = [$scope.key.toLowerCase()];

            if ($scope.state != 'missing_left') {
                config = $scope.application.config_items[$scope.key];
                if (typeof config.value !== 'undefined') {
                    watchedFields.push(config.value.toLowerCase());
                }
            }

            if ($scope.state != 'missing_right' && typeof $scope.otherApplication !== 'undefined') {
                config = $scope.getConfig($scope.otherApplication.config_items, $scope.key);
                if (typeof config.value !== 'undefined') {
                    watchedFields.push(config.value.toLowerCase());
                }
            }

            var isSubstring = false;
            watchedFields.forEach(function (item) {
                if (isSubstring === false) {
                    isSubstring = (item.indexOf(filter) !== -1);
                }
            });

            // If the filter value is not a substring of the
            // name, we have to exclude it from view.
            var isExcludedByFilter = !isSubstring;

            if (filters.states.length > 0) {
                isExcludedByFilter = isExcludedByFilter || filters.states.indexOf($scope.states[$scope.key]) == -1;
            }

            $scope.isExcludedByFilter = isExcludedByFilter;

            return $scope.isExcludedByFilter;
        }
    }]);
})(window.config_store, _, $);
