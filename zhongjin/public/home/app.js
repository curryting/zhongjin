
require.config({
    baseUrl: '/home/script/',
    paths: {
        // libs
        jquery: 'lib/jquery/jquery-2.2.4.min',          // jquery
        validate: 'lib/validate/jquery-validate.min',   // jquery表单验证验证插件
        // validateMsg: 'lib/validate/messages-zh',        // 验证中文提示插件 含validate的默认配置
        additionalMethods: 'lib/validate/additional-methods',
        bootstrapTable: 'lib/bootstrap-table/bootstrap-table.min',        // bootstrap表格插件
        UE: 'lib/ueditor/ueditor.all.min',              // 百度富文本编辑器
        ueconfig: 'lib/ueditor/ueditor.config',         // 百度富文本编辑器配置
        xiumiV5: 'lib/ueditor/xiumi-ue-dialog-v5',      // 秀米插件
        uelang: 'lib/ueditor/lang/zh-cn/zh-cn',         // 百度富文本编辑器中文插件
        ueZeroClipboard: 'lib/ueditor/third-party/zeroclipboard/ZeroClipboard.min',
        jedate: 'lib/jedate/jquery.jedate.min',         // 日期插件
        // text: 'lib/requirejs/require-text.min',         // require加载非js文件
        Router: 'lib/hash-router/hash-router',          // 路由插件
        plupload: 'lib/plupload/plupload.full.min',          // 文件上传插件

        // common
        service: 'common/service',              // API
        utils: 'common/utils',                  // 公用函数
        routerConfig: 'common/router-config',   // 路由配置
        CreatePop: 'common/createpop',          // 弹窗组件
        DAO: 'common/dao',                      // 数据访问对象 [临时保存前端操作]
        BST: 'common/bstable-ext',              // 二次封装bootstrap table
        tree: 'common/tree',                    // 组织成员
        // module
        index: 'module/index',          // 首页
        company: 'module/company',      // 公司
        client: 'module/client',        // 客户
        product: 'module/product',      // 产品
        news: 'module/news',            // 新闻
        notice: 'module/notice',        // 通知
        vote: 'module/vote',            // 投票
        survey: 'module/survey',        // 调研
        invest: 'module/invest',        // 投资
        setting: 'module/setting',      // 设置

    },
    shim: {
        UE: { deps: ['ueconfig'] },
        xiumiV5: { deps: ['UE'] },
        ueZeroClipboard: { exports: 'ZeroClipboard' },
        uelang: { deps: ['UE'] },
        validate: { deps: ['jquery'] },
        validateMsg: { deps: ['jquery', 'validate'] },
        jedate: { deps: ['jquery'] },
        Router: { exports: 'Router' },
    }
});

require([
    'jquery',
    'service',
    'utils',
    'DAO',
    'CreatePop',
    'ueZeroClipboard',
    'routerConfig',
    // 'xiumiV5'
], function ($, service, utils, DAO, CreatePop, ZeroClipboard) {
    // 这样修复baidu富文本编辑器的bug
    console.log('hello shinekidd !')
    // 清空bstable的筛选条件
    var BSTdao = new DAO('BST');
    window.BSTdao = BSTdao;
    BSTdao.clearFilter = function() {
        this.remove('searchArgs');
        this.remove('pageNumber');
    }
    var oldPageName = window.location.hash.split('/')[1];
    window.addEventListener('hashchange', function(e) {
        // console.log(e)
        var nowPageName = window.location.hash.split('/')[1];
        if (oldPageName === nowPageName) {
            return;
        }
        oldPageName = nowPageName;
        BSTdao.clearFilter();
    });

    var userName = window.sessionStorage.getItem('username');
    $('#j_login_username').text(userName);

    window.ZeroClipboard = ZeroClipboard;
    // 退出登录
    $('#j_logout').on('click', function () {
        service.sign.logout().done(function (res) {
            if (res.code === 200) {
                // 清空bstable的筛选条件
                BSTdao.remove('searchArgs');
                BSTdao.remove('pageNumber');
                setTimeout(function() {
                    window.location = '/login';
                }, 20);
            }
        });
    });
    // 修改密码
    var xxx = new CreatePop({
        type: 'prompt',
        name: 'resetPass',
        autoHide: false,
        title: '修改密码',
        blast: '#j_reset_key',
        afterMountCallBack: function () {
            var that = this;
            require(['validate'], function () {
                that.$form.validate({
                    submitHandler: function (form) {
                        var obj = $(form).serialize();
                        service.sign.resetPass(obj).done(function (res) {
                            res.code === 200 && CreatePop.tip('密码修改成功').sure(function () { window.location = '/login' });
                        });
                        that.hide();
                    }
                });
            });
        }
    });
});

