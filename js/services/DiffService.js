(function (configStore, _) {
    "use strict";
    configStore.service(
        'DiffService',
        function () {
            var diffCache = {};

            return {
                getDiff: getDiff,
                getGroupsDiffStatus: getGroupsDiffStatus,
                canCompare: canCompare
            };

            function canCompare(app1, app2) {
                var group1 = app1.group;
                var group2 = app2.group;

                return group1 === group2;
            }

            function getDiff(app1, app2) {
                if (typeof(String.prototype.localeCompare) === 'undefined') {
                    String.prototype.localeCompare = function (str) {
                        return ((this == str) ? 0 : ((this > str) ? 1 : -1));
                    };
                }

                if (typeof app2 == 'undefined') {
                    return null;
                }

                if (canCompare(app1, app2)) {
                    throw "Incompatible apps. Can't obtain a diff for apps which aren't in the same group.";
                }

                var diffCacheKey = getDiffCacheKey(app1, app2);

                if (diffCache.hasOwnProperty(diffCacheKey)) {
                    return diffCache[diffCacheKey];
                }

                var diff = {
                    app_left: app1,
                    app_right: app2,
                    keys_union: [],
                    identical: [],
                    different: [],
                    missing_left: [],
                    missing_right: []
                };

                var configs1 = app1.config_items;
                var keys1 = Object.keys(configs1);
                keys1.sort();
                var count1 = keys1.length;

                var configs2 = app2.config_items;
                var keys2 = Object.keys(configs2);
                keys2.sort();
                var count2 = keys2.length;

                diff.keys_union = _.union(keys1, keys2);
                diff.keys_union.sort();

                var index1 = 0, index2 = 0;

                while (true) {
                    if (index1 >= count1 && index2 >= count2) {
                        break;
                    }

                    if (index1 >= count1) {
                        diff.missing_left.push(keys2[index2]);
                        index2 += 1;

                        continue;
                    }

                    if (index2 >= count2) {
                        diff.missing_right.push(keys1[index1]);
                        index1 += 1;

                        continue;
                    }

                    var key1 = keys1[index1];
                    var key2 = keys2[index2];

                    var comparison = key1.localeCompare(key2);

                    if (0 === comparison) {
                        var value1 = configs1[key1];
                        var value2 = configs2[key2];

                        if (value1 === value2) {
                            diff.identical.push(key1);
                        } else {
                            diff.different.push(key1);
                        }

                        index1 += 1;
                        index2 += 1;
                    } else if (0 > comparison) {
                        diff.missing_right.push(key1);
                        index1 += 1;
                    } else if (0 < comparison) {
                        diff.missing_left.push(key2);
                        index2 += 1;
                    }
                }

                diffCache[diffCacheKey] = diff;

                return diff;
            }

            function getGroupsDiffStatus(groups) {
                var status = {};

                _.forEach(groups, function (group) {
                    status[group.id] = {
                        missingCounter: 0,
                        newCounter: 0,
                        ref: 0,
                        in_sync: 0
                    };

                    if (group.reference) {
                        _.forEach(group.apps, function (app) {
                            if (app.id === group.reference.id) {
                                status[group.id].ref += 1;
                                return;
                            }

                            var diff = getDiff(app, group.reference);

                            if (diff.missing_left.length > 0) {
                                status[group.id].missingCounter += 1;
                            }

                            if (diff.missing_right.length > 0) {
                                status[group.id].newCounter += 1;
                            }

                            if (diff.missing_left.length === 0 && diff.missing_right.length === 0) {
                                status[group.id].in_sync += 1;
                            }
                        });
                    }
                });

                return status;
            }

            function getDiffCacheKey(app, app2) {
                return 'diff_' + app.id + '_' + app2.id;
            }
        });
})(window.config_store, _, angular);
