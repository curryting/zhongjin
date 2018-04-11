define([
    'jquery', 'service', 'utils', 'CreatePop', 'xiumiV5'
], function ($, service, utils, CreatePop) {
    'use strict';
    return {
        profile: function (args) {
           var ue = utils.initUE(UE, 'j_u_editor', {
                autoHeightEnabled: false,
                initialFrameWidth: window.innerWidth > 1900 ? 1100: 800,
                initialFrameHeight: 600,
                maximumWords: 10000000,
            });
            //获取公司简介;
            service.company.getProfile().done(function (res) {
                ue.ready(function () {
                    ue.setContent(res.data.content);
                });
            });
            // 更新公司简介
            $('#j_profile_form').on('submit', function () {
                if (!$.sendOK) { return; }
                var opt = $(this).serialize();
                service.company.setProfile(opt).done(function (res) {
                    res.code === 200 && CreatePop.tip('保存成功！');
                });
            });
            // 更新预览
            ue.addListener('contentChange', function () {
                $('#j_preview').html(ue.getContent());
            });
        }
    }
});