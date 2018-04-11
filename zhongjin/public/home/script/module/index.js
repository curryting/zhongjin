define(['jquery', 'service', 'utils', 'CreatePop'], function ($, service, utils, CreatePop) {
    'use strict';

    return {
        home: function () {
            // 获取统计数据，并绑定到DOM
            service.home.getStatistic().done(function(res) {
                res.code === 200 && utils.bindDataToDOM (res.data, 'data', '#j_load_container');
            });
        }
    };
});