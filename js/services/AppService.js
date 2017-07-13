(function (configStore, _, angular) {
    "use strict";
    configStore.service(
        'AppService',
        ['$http', '$q', 'SecurityTokenService',
            function ($http, $q, SecurityTokenService) {
                var exposedFunctions = {
                    getApp: getApp,
                    getApps: getApps,
                    deleteApp: deleteApp,
                    createApp: createApp,
                    editApp: editApp,
                    revokeApiKey: revokeApiKey
                };

                return exposedFunctions;

                function getApps(appSlugs) {
                    var finalDefer = $q.defer();
                    var promises = [];
                    _.forEach(appSlugs, function (slug) {
                        var d = $q.defer();
                        exposedFunctions.getApp(slug).then(function (app) {
                            d.resolve(app);
                        });

                        promises.push(d.promise);
                    });

                    $q.all(promises).then(function(data) {
                        var apps = {};
                        _.forEach(data, function (app) {
                            apps[app.slug] = app;
                        });

                        finalDefer.resolve(apps);
                    });

                    return finalDefer.promise;
                }

                function getApp(appSlug) {
                    var request = $http({
                        method: 'GET',
                        url: '/apps/' + appSlug
                    });

                    return request.then(handleSuccess, handleError);
                }

                function createApp(app, duplicateConfigFromApp) {
                    return SecurityTokenService.getSecurityToken('app', 'createApp').then(
                        function (securityToken) {
                            var requestObject = {
                                method: 'POST',
                                url: '/apps',
                                params: {_token: securityToken},
                                data: {app: app}
                            };

                            if (duplicateConfigFromApp) {
                                requestObject.params.duplicateConfigFromApp = duplicateConfigFromApp.slug;
                            }

                            var request = $http(requestObject);

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function editApp(app) {
                    return SecurityTokenService.getSecurityToken('app', 'updateApp').then(
                        function (securityToken) {
                            var configItems = _.reduce(app.config_items, function (results, config) {
                                if (typeof config != "undefined") {
                                    results[config.index] = {
                                        key: config.key,
                                        value: config.value
                                    };
                                }

                                return results;
                            }, {});

                            var request = $http({
                                method: 'PUT',
                                url: '/apps/' + app.slug,
                                params: {_token: securityToken},
                                data: {
                                    app: {
                                        name: app.name,
                                        description: app.description,
                                        group: app.group ? app.group.id : null,
                                        config_items: configItems
                                    }
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function deleteApp(app) {
                    return SecurityTokenService.getSecurityToken('app', 'deleteApp').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'DELETE',
                                url: '/apps/' + app.slug,
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function revokeApiKey(app) {
                    return SecurityTokenService.getSecurityToken('app', 'revokeApiKey').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'DELETE',
                                url: '/apps/' + app.slug + '/api-key',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function handleError(response) {
                    if (!angular.isObject(response.data)) {
                        return ($q.reject("An unknown error occurred."));
                    }

                    return $q.reject(response.data);

                }

                function handleSuccess(response) {
                    return response.data;
                }
            }]);
})(window.config_store, _, angular);
