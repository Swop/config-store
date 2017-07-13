(function(configStore, _, $) {
    "use strict";

    configStore.filter('filterByAppStatus', function() {
        function isRef(app, group) {
            return (group.reference && group.reference.id === app.id);
        }
        function hasRef(group) {
            return (group && group.reference);
        }

        return function(apps, group, diff, status) {
            return _.filter(apps, function (app) {
                if (!group || !hasRef(group)) {
                    return true;
                }

                if (status === 'ref') {
                    return isRef(app, group);
                } else if (status === 'error') {
                    return !isRef(app, group) && diff[app.id].missing_left.length > 0;
                } else if (status === 'warning') {
                    return !isRef(app, group) && diff[app.id].missing_left.length === 0 && diff[app.id].missing_right.length > 0;
                } else if (status === 'good') {
                    return !isRef(app, group) && diff[app.id].missing_left.length === 0 && diff[app.id].missing_right.length === 0;
                } else {
                    return true;
                }
            });
        };
    });
})(window.config_store, _, $);
