// jquery Ajax 全局设置
define(['jquery', 'CreatePop'], function ($, CreatePop) {
    'use strict';
    // ajax global settings
    $.ajaxSetup({
        global: true,
        cache: false,
        // timeout: 2000,
        beforeSend: function (xhr) {
            $.sendOK = false; // throttle ajax
        },
        dataType: 'json',
        error: function (xhr, status, err) {
            $.sendOK = true; // throttle ajax
            console.log('ajax status: ', status);
            console.error('ajax error: ', err)
            var msg = '';
            switch (xhr.status) {
                case 0:
                    msg = xhr.statusText;
                    break;
                case 401:
                    msg = '没有访问权限';
                    break;
                case 403:
                    msg = '拒绝访问';
                    break;
                case 404:
                    msg = '找不到资源';
                    break;
                case 500:
                    msg = '服务器错出';
                    break;
                default:
                    msg = '未知的错误';
            }
            msg && CreatePop.error(msg);
        },
        success: function (result, status, xhr) {
            // console.log('ajax status: ', status);
            $.sendOK = true; // throttle ajax
            result.code !== 200 
            && result.msg 
            && CreatePop.warning(result.msg)
            .afterHide(function () { 
                if (result.code === 402) {
                    window.sessionStorage.setItem('_history_hash', window.location.hash);
                    window.location = '/login';
                }
            });
        }
    });

    var service = {
        // 登录页
        sign: {
            // 注销
            logout: function (opt) { return $.post('/home/user/logout', opt) },
            // 修改密码
            resetPass: function (opt) { return $.post('/home/user/modify', opt) },
        },
        // 首页
        home: {
            // 统计数据
            getStatistic: function (opt) { return $.post('/home/index/index', opt) }
        },
        // 公司简介
        company: {
            // 获取简介
            getProfile: function (opt) { return $.post('/home/profile/detail', opt) },
            // 更新简介
            setProfile: function (opt) { return $.post('/home/profile/update', opt) }
        },
        // 客户管理
        client: {
            /** 客户列表 **/
            // 获取投资产品类别
            getProductTypeList: function (opt) { return $.post('/home/product/lists', opt) },
            // 获取客户列表
            getClientList: '/home/customer/lists',
            // 添加客户
            addClient: function (opt) { return $.post('/home/customer/add', opt) },
            // 修改客户
            updateClient: function (opt) { return $.post('/home/customer/update', opt) },
            // 删除客户
            deleteClient: function (opt) { return $.post('/home/customer/delete', opt) },
            // 上传导入客户xls
            uploadClientFile: '/home/customer/upload',
            // 回传客户文件id
            sendCustomerFileId: function (opt) { return $.post('/home/customer/import', opt) },

            /** 客户详情 **/
            // 获取客户详细信息
            getClientDetail: function (opt) { return $.post('/home/customer/detail', opt) },
            // 获取客户投资列表
            getClientInvetment: '/home/customer/getInvestProduct',
            // 添加投资
            addInvestment: function (opt) { return $.post('/home/investment/add', opt) },
            // 上传报告文件
            uploadReportFile: '/home/investment/uploadReport',
            // 上传报告文件 回传参数
            sendReportFileOpt: function (opt) { return $.post('/home/investment/importReport', opt) },
            // 获取投资报告列表
            getReport: '/home/investment/reportLists',
            // 赎回
            redeem: function (opt) { return $.get('/home/investment/redeem', opt) },
            // 转让
            transfer: function (opt) { return $.post('/home/investment/transfer', opt) },
            // 获取客户名称列表
            getClientNameList: function (opt) { return $.post('/home/customer/getName', opt) },
        },
        // 产品管理
        product: {
            // 添加产品
            addProdcut: function (opt) { return $.post('/home/product/add', opt) },
            // 获取产品列表
            getProductList: '/home/product/lists',
            // 产品详情
            getProductDetail: function (opt) { return $.post('/home/product/detail', opt) },
            // 更新产品
            updateProduct: function (opt) { return $.post('/home/product/update', opt) },
            // 删除产品
            delProduct: function (opt) { return $.post('/home/product/delete', opt) },

        },
        // 新闻管理
        news: {
            // 添加
            addNews: function (opt) { return $.post('/home/news/add', opt) },
            // 上传封面
            uploadCover: '/home/news/upload',
            // 微信预览
            previewNews: function (opt) { return $.post('/home/news/preview', opt) },
            // 获取列表
            getNewsList: '/home/news/lists',
            // 详情
            getNewsDetail: function (opt) { return $.post('/home/news/detail', opt) },
            // 删除新闻
            deleteNews: function (opt) { return $.post('/home/news/delete', opt) },
            // 获取评论
            getComments: '/home/news/readComment', 
            // 获取评论的回复
            getCommentReply: '/home/news/readReply',
            // 获取已读名单
            getHadReadList: '/home/news/readMember', // post
            // 获取未读名单
            getNotReadList: '/home/news/unreadMember', // post
            // 未读提醒
            remindRead: '/home/news/remind', // post
        },
        // 通知管理
        notice: {
            // 添加
            addNotice: function (opt) { return $.post('/home/notice/add', opt) },
            // 上传封面
            uploadCover: '/home/notice/uploadImg',
            // 上传通知文件
            uploadFile: '/home/notice/uploadFile',
            // 微信预览
            previewNotice: function (opt) { return $.post('/home/notice/preview', opt) },
            // 获取列表
            getNoticeList: '/home/notice/lists',
            // 详情
            getNoticeDetail: function (opt) { return $.post('/home/notice/detail', opt) },
            // 撤销
            revokeNotice: function (opt) { return $.post('/home/notice/revoke', opt) },
            // 删除
            deleteNotice: function (opt) { return $.post('/home/notice/delete', opt) },
            // 获取评论
            getComments: '/home/notice/readComment',
            // 获取评论的回复
            getCommentReply: '/home/notice/readReply', 
            // 获取已读名单
            getHadReadList: '/home/notice/readMember',
            // 获取未读名单
            getNotReadList: '/home/notice/unreadMember',
            // 未读提醒
            remindRead: '/home/notice/remind',
        },
        // 投票管理
        vote: {
            // 添加投票内容
            addVoteContent: function (opt) { return $.post('/home/vote/add', opt) },
            // 添加投票选项
            addVoteOption: function(opt) { return $.post('/home/vote/save', opt) },
            // 保存，更新投票设置
            saveVoteSetting: function(opt) { return $.post('/home/vote/setup', opt)},

            // 获取投票内容
            getVoteContent: function (opt) { return $.post('/home/vote/editData', opt) },
            // 获取投票选项
            getVoteOption: function (opt) { return $.post('/home/vote/editQuestionData', opt) },
            // 获取投票设置
            getVoteSetting: function (opt) { return $.post('/home/vote/setupData', opt) },
            
            // 更新投票内容
            updateVoteContent: function (opt) { return $.post('/home/vote/edit', opt) },
            // 更新投票选项
            updateVoteOption: function (opt) { return $.post('/home/vote/editQuestion', opt) },

            // 上传封面
            uploadCover: '/home/vote/upload',

            // 微信预览
            previewVote: function (opt) { return $.post('/home/vote/preview', opt) },
            // 获取列表
            getVoteList: '/home/vote/lists',
            // 删除
            deleteVote: function (opt) { return $.post('/home/vote/delete', opt) },
            // 发布投票
            publishVote: function (opt) { return $.post('/home/vote/publish', opt) },
            // 获取统计数据
            getStatistics: function (opt) { return $.post('/home/vote/statis', opt) },

        },
        // 调研管理
        survey: {
            // 新建（调研内容）
            addSurveyContent: function (opt) { return $.post('/home/survey/add', opt) },
            // 新建（调研表单）
            addSurveyOption: function(opt) { return $.post('/home/survey/save', opt) },

            // 获取（调研内容）
            getSurveyContent: function(opt) { return $.post('/home/survey/editData', opt) },
            // 获取（调研表单）
            getSurveyOption: function(opt) { return $.post('/home/survey/editQuestionData', opt) },

            // 更新调研内容
            updateSurveyContent: function (opt) { return $.post('/home/survey/edit', opt) },
            // 更新投票选项
            updateSurveyOption: function (opt) { return $.post('/home/survey/editQuestion', opt) },

            // 上传封面
            uploadCover: '/home/survey/upload',
            // 获取列表
            getSurveyList: '/home/survey/lists',
            // 统计
            getStatistics: function (opt) { return $.post('/home/survey/statis', opt) },
            // 删除通知
            deleteSurvey: function (opt) { return $.post('/home/survey/delete', opt) },
            // 预览
            previewSurvey: function (opt) { return $.post('/home/survey/preview', opt) },
            // 发布
            publishSurvey: function (opt) { return $.post('/home/survey/publish', opt) },
            // 答卷列表
            getPaperList: '/home/survey/answerAll',
            // 答卷详情
            getPaperDetail: function (opt) { return $.post('/home/survey/answerDetailByMem', opt) },
        },
        // 投资管理
        investment: {
            // 获取支持的银行列表
            // getBankList: function (opt) { return $.get(host + '/api/zj/crm/investment/bank/list', opt) },
            // 获取产品类型列表
            getProductTypeList: function (opt) { return service.client.getProductTypeList(opt) },
            // 获取客户名称列表
            getClientNameList: function (opt) { return service.client.getClientNameList(opt) },
            // 添加投资
            addInvestment: function (opt) { return $.post('/home/investment/add', opt) },
            // 添加客户
            addClient: function (opt) { return service.client.addClient(opt) },
            // 获取投资列表
            getInvestList: '/home/investment/lists',
            // 查看投资详情
            getInvestDetail: function (opt) { return $.post('/home/investment/detail', opt) },
            // 删除一条投资
            deleteInvestment: function (opt) { return $.post('/home/investment/delete', opt) },
            // 导入投资
            importInvestment: '/home/investment/upload',
            // 回传文件id
            sendInvestFileId: function (opt) { return $.post('/home/investment/import', opt) },

        },
        // 应用设置
        setting: {},
        // 组织目录
        org: {
            // 组织目录
            getStructure: function(opt) { return $.post('/home/product/lists', opt) },
            // 组织成员
            getMember: function(opt) { return $.post('/home/customer/getNameByProduct', opt) },
            // 搜索成员
            search: function(opt) { return $.post('/home/customer/getNameJoinInvestment', opt) }
        }
    }
    return service
});
