(function (configStore) {
    "use strict";
    configStore.service(
        'GroupService',
        ['$http', '$q', 'SecurityTokenService',
            function ($http, $q, SecurityTokenService) {
                return ({
                    dropRefStatus: dropRefStatus,
                    dispatchConfig: dispatchConfig,
                    updateRef: updateRef,
                    getConcurrentConfigValues: getConcurrentConfigValues,
                    getGroup: getGroup,
                    createGroup: createGroup,
                    editGroup: editGroup,
                    getGroups: getGroups,
                    deleteGroup: deleteGroup
                });

                function getConcurrentConfigValues(groupId, configItemKey) {
                    var request = $http({
                        method: 'GET',
                        url: '/groups/' + groupId + '/competitors/' + configItemKey
                    });

                    return request.then(handleSuccess, handleError);
                }

                function dispatchConfig(groupId, configKey, configValues) {
                    return SecurityTokenService.getSecurityToken('group', 'dispatchConfig').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/groups/' + groupId + '/dispatch-config',
                                params: {_token: securityToken},
                                data: {
                                    configKey: configKey,
                                    configValues: configValues
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function updateRef(groupId, app) {
                    return SecurityTokenService.getSecurityToken('group', 'setRef').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'PUT',
                                url: '/groups/' + groupId + '/ref',
                                params: {_token: securityToken},
                                data: {
                                    appSlug: app.slug
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function dropRefStatus(groupId) {
                    return SecurityTokenService.getSecurityToken('group', 'dropRef').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'DELETE',
                                url: '/groups/' + groupId + '/ref',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function getGroup(groupId) {
                    var request = $http({
                        method: 'GET',
                        url: '/groups/' + groupId
                    });

                    return request.then(handleSuccess, handleError);
                }

                function getGroups() {
                    var request = $http({
                        method: 'GET',
                        url: '/groups/ajax'
                    });

                    return request.then(handleSuccess, handleError);
                }

                function createGroup(group) {
                    return SecurityTokenService.getSecurityToken('group', 'createGroup').then(
                        function (securityToken) {
                            var requestObject = {
                                method: 'POST',
                                url: '/groups',
                                params: {_token: securityToken},
                                data: {appGroup: group}
                            };

                            var request = $http(requestObject);

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function editGroup(group) {
                    return SecurityTokenService.getSecurityToken('group', 'updateGroup').then(
                        function (securityToken) {
                            var sentGroup = {
                                name: group.name
                            };

                            var request = $http({
                                method: 'PUT',
                                url: '/groups/' + group.id,
                                params: {
                                    _token: securityToken
                                },
                                data: {
                                    appGroup: sentGroup
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function deleteGroup(group, deleteGroupApps) {
                    return SecurityTokenService.getSecurityToken('group', 'deleteGroup').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'DELETE',
                                url: '/groups/' + group.id,
                                params: {
                                    deleteGroupApps: deleteGroupApps ? '1' : '0',
                                    _token: securityToken
                                }
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
})(window.config_store);
