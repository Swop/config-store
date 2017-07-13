(function(configStore) {
    "use strict";
    configStore.service('ConfigPreviewService', ['$http', '$q', '$modal', function($http, $q, $modal) {
        return({
            openPreviewModal: openPreviewModal
        });

        function openPreviewModal(app) {
            $modal.open({
                templateUrl: '/templates/modals/PreviewModal.html',
                controller: function ($scope, $modalInstance) {
                    $scope.error = null;
                    $scope.preview = null;

                    $http({
                        method: 'GET',
                        url: '/apps/' + app.slug + '/preview'
                    })
                        .then(handleSuccess, handleError)
                        .then(
                        function (data) {
                            if (data.hasOwnProperty('JSON')) {
                                // Pretty-print the JSON data
                                data.JSON = JSON.stringify(JSON.parse(data.JSON), null, 2);
                            }

                            $scope.preview = data;
                        },
                        function (error) {
                            $scope.error = error;
                        }
                    );

                    $scope.close = function () {
                        $modalInstance.dismiss('close');
                    };
                },
                resolve: {
                    app: function () {
                        return app;
                    }
                }
            });
        }

        function handleError(response) {
            if (!angular.isObject(response.data)) {
                return($q.reject( "An unknown error occurred."));
            }

            return $q.reject(response.data);

        }

        function handleSuccess(response) {
            return response.data;
        }
    }]);
})(window.config_store);
