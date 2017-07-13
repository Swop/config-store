(function (configStore) {
    "use strict";
    configStore.service(
        'UserService',
        ['$http', '$q', 'SecurityTokenService',
            function ($http, $q, SecurityTokenService) {
                var me;
                var currentUserInitialized = false;

                return ({
                    getUsers: getUsers,
                    getUser: getUser,
                    getMe: getMe,
                    createUser: createUser,
                    updateUser: updateUser,
                    activateUser: activateUser,
                    deactivateUser: deactivateUser,
                    promoteUser: promoteUser,
                    demoteUser: demoteUser,
                    changePassword: changePassword
                });

                function getUsers() {
                    var request = $http({
                        method: 'GET',
                        url: '/users'
                    });

                    return request.then(handleSuccess, handleError);
                }

                function getUser(userSlug) {
                    var request = $http({
                        method: 'GET',
                        url: '/users/' + userSlug
                    });

                    return request.then(handleSuccess, handleError);
                }

                function getMe() {
                    return $q(function (resolve, reject) {
                        if (!currentUserInitialized) {
                            var request = $http({
                                method: 'GET',
                                url: '/me'
                            });

                            return request.then(handleSuccess, handleError).then(
                                function (data) {
                                    me = data.id ? data : null;
                                    currentUserInitialized = true;

                                    resolve(me);
                                },
                                function (error) {
                                    reject(error);
                                }
                            );
                        } else {
                            resolve(me);
                        }
                    });
                }

                function createUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'createUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/users',
                                params: {_token: securityToken},
                                data: {
                                    user: user
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function updateUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'updateUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'PUT',
                                url: '/users/' + user.slug,
                                params: {_token: securityToken},
                                data: {
                                    user: {
                                        name: user.name,
                                        username: user.username,
                                        email: user.email
                                    }
                                }
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function activateUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'activateUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/users/' + user.slug + '/activate',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function deactivateUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'deactivateUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/users/' + user.slug + '/deactivate',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function promoteUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'promoteUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/users/' + user.slug + '/promote',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function demoteUser(user) {
                    return SecurityTokenService.getSecurityToken('user', 'demoteUser').then(
                        function (securityToken) {
                            var request = $http({
                                method: 'POST',
                                url: '/users/' + user.slug + '/demote',
                                params: {_token: securityToken}
                            });

                            return request.then(handleSuccess, handleError);
                        }
                    );
                }

                function changePassword(user, newPassword, oldPassword) {
                    return SecurityTokenService.getSecurityToken('user', 'changePassword').then(
                        function (securityToken) {
                            var data = {
                                newPassword: newPassword
                            };

                            if (oldPassword) {
                                data.oldPassword = oldPassword;
                            }

                            var request = $http({
                                method: 'PUT',
                                url: '/users/' + user.slug + '/password',
                                params: {_token: securityToken},
                                data: data
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
