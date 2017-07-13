(function (configStore, _, $) {
    "use strict";
    configStore.controller(
        'AppConfigEditCtrl',
        ['$scope', '$window', '$compile', 'AppService',
            function ($scope, $window, $compile, AppService) {
                $scope.application = {};
                $scope.displaySearch = true;
                $scope.actionBar = 'full';
                $scope.filters = {
                    query: "",
                    states: []
                };

                $scope.init = function () {
                    var appSlugs = [$scope.slug];
                    if ($scope.comparedAppSlug) {
                        appSlugs.push($scope.comparedAppSlug);
                    }

                    AppService.getApps(appSlugs).then(function (apps) {
                        _.forEach(apps, function (app, slug) {
                            app.config_items = _.reduce(app.config_items, function (results, config) {
                                results[config.key] = config;

                                return results;
                            }, {});
                        });

                        $scope.application = apps[$scope.slug];

                        if ($scope.comparedAppSlug) {
                            $scope.comparedApp = apps[$scope.comparedAppSlug];
                        }

                        var appMarkup = '<config-items-edit ' +
                            'app="application" ' +
                            'compared-app="comparedApp" ' +
                            'display-search="displaySearch" ' +
                            'filters="filters" ' +
                            'action-bar="actionBar" />';

                        $compile(appMarkup)($scope).appendTo(angular.element('#app-container'));
                    });
                };
            }]);
})(window.config_store, _, $);
