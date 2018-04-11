define([
    'jquery',
    'service',
    'utils',
    'CreatePop',
    'DAO',
    'BST',
    'plupload',
    'validate',
    'additionalMethods',
    // 'validateMsg',
    'jedate'
], function ($, service, utils, CreatePop, DAO, BST, plupload) {
    'use strict';
    /*********** 模块公用函数 ***********/
    // 数据中心 map data
    var scope = {
        // 产品类型
        productTypeList: [],
        // 客户列表
        clientBST: {},

        // 标记当前页面视图名称
        pageView: 'list',
        // 客户详情信息
        clientDetail: {},
        //  产品表格
        productBST: {},

    };
    // 添加/修改客户DOM事件
    scope.forClientPopEvent = function (that) {
        // 切换为添加机构
        $('#org' + that.normalID).on('click', function () {
            $('#j_company' + that.normalID).removeClass('hide').find('input').removeAttr('disabled');
        });
        // 切换为添加个人
        $('#person' + that.normalID).on('click', function () {
            $('#j_company' + that.normalID).addClass('hide').find('input').attr('disabled', true);
        });
    }

    // 获取产品类型
    function getProducts() {
        service.client.getProductTypeList({ offset: -1, status: 4 }).done(function (res) {
            // map data
            res.code === 200 && (scope.productTypeList = res.data.list);
            $.publish('getNecessaryOK');
            // console.log(res1, res2, res3)
        }).fail(function (err) {
            console.error(err)
        });
    }

    // 修改客户信息pop
    var updateClientPop = new CreatePop({
        type: 'prompt',
        name: 'addClient',
        title: '修改客户',
        autoHide: false,
        sureBtn: '保存',
        afterMountCallBack: function () {
            // bind toggle company input event
            scope.forClientPopEvent(this);
            var that = this;
            $(this.formID).validate({
                submitHandler: function (form) {
                    if (!$.sendOK) { return; }
                    that.hide();
                    var obj = scope.clientDetail = utils.getFormData(that.formID); // map data
                    service.client.updateClient(obj).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('修改成功');      // tip
                            obj = JSON.parse(JSON.stringify(obj));
                            // 更新数据
                            if (scope.pageView === 'detail') { // 点击了详情里的修改按钮
                                // 转化id为文字
                                obj.client_type = utils.parseClientType(obj.client_type);
                                obj.id_type = utils.parseIdType(obj.id_type);
                                utils.bindDataToDOM(obj, 'intro', '#j_client_intro');
                            } else { // 点击了客户列表里的修改按钮
                                $.publish('BSTupdate', [obj]);
                            }
                        }
                    });
                }
            });
        },
        beforeShowCallBack: function () {
            if (scope.pageView === 'list') { // 在客户列表页修改
                var obj = scope.clientBST.clickEve.row;
            } else {    // 在客户详情也修改
                var obj = scope.clientDetail;
            }
            obj = JSON.parse(JSON.stringify(obj)); // 避免修改了scope中的对象属性
            this.$form[0].reset();
            utils.setFormData(this.formID, obj); // 绑定数据
            if (obj.client_type == 2) { // 机构客户
                $('#j_company' + this.normalID).removeClass('hide').find('input').removeAttr('disabled');
            } else { // 个人客户
                $('#j_company' + this.normalID).addClass('hide').find('input').attr('disabled', true);
            }
        },
    });
    // 添加投资pop
    var addInvestmentPop = new CreatePop({
        type: 'prompt',
        name: 'addInvest',
        title: '添加投资',
        blast: '.j-handle-invest, #j_add_invest',
        delegate: '#j_load_container',
        autoHide: false,
        afterMountCallBack: function () {
            // 日期插件
            $(this.popID).find('.time-input').jeDate({
                format: 'YYYY-MM-DD',
                skinCell: "jedatered",
                isTime: false,
            });
            // // 表单校验
            var that = this;
            $(that.formID).validate({
                submitHandler: function (form) { // 表单提交回调
                    if (!$.sendOK) { return; }
                    that.hide();
                    // var obj = utils.getFormData(form);
                    service.client.addInvestment(that.$form.serialize()).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('投资成功！');
                            if (scope.pageView === 'detail') { // 在客户详情页投资，刷新表格
                                scope.productBST.refresh({ clearFilter: true, page: 1 });
                            }
                        }
                    });
                }
            });
        },
        beforeShowCallBack: function () {
            this.$form[0].reset();
            if (scope.pageView === 'list') { // 在客户列表页投资
                var obj = scope.clientBST.clickEve.row;
            } else {    // 在客户详情也投资
                var obj = scope.clientDetail;
            }
            obj = JSON.parse(JSON.stringify(obj));
            obj.id_type = utils.parseIdType(String(obj.id_type));
            utils.bindDataToDOM(obj, 'clientInfo', this.popID); // 绑定客户信息
        },
    });

    /* 路由执行函数 */
    return {
        /******* 客户列表页 *****/
        list: function (args) {
            // entry client list page
            scope.pageView = 'list';
            // 配置客户列表 
            scope.clientBST = BST.extOption({
                bsTableId: '#j_client_table',
                url: service.client.getClientList,
                BSTSearchFromId: '#j_form_search',
                BSTSearchOBJPrefix: 'search',
            });
        
            // 获取产品列表
            getProducts();
            // 绑定必要数据
            $.unsubscribe('getNecessaryOK');
            $.subscribe('getNecessaryOK', function () {
                if (scope.pageView !== 'list') {
                    return;
                }
                var productList = scope.productTypeList.filter(function (ele, index, arr) {
                    return ele.status != 3;
                }, this);
                // 生成添加投资的下拉列表
                utils.repeatDOM(productList, 'product', addInvestmentPop.popID, true, 'replace', 2);
                // 生成搜索栏投资产品select
                utils.repeatDOM(productList, 'productType', '#j_form_search');
                // 发布事件让 bs table还原上一次的搜索条件
                $.publish('BSTRstoreSearch');
            });

            /********** 模态框 **********/

            // 添加客户pop
            var addClientPop = new CreatePop({
                type: 'prompt',
                name: 'addClient',
                title: '添加客户',
                blast: '#j_add_client',
                autoHide: false,
                afterMountCallBack: function () {
                    var that = this;
                    // 生成证件类型列表
                    that.$form.find('input[name="id"]').attr('disabled', true); // 禁用隐藏的id输入框
                    scope.forClientPopEvent(that); // bind toggle company input event
                    $(that.formID).validate({ // validate form and submit
                        submitHandler: function (form) {
                            if (!$.sendOK) { return; }
                            var obj = utils.getFormData('#'+that.$form.attr('id'));
                            service.client.addClient(obj).done(function (res) {
                                if (res.code === 200) {
                                    that.hide();
                                    // var row = $.extend(obj, { id: res.data.id });
                                    // $.publish('BSTadd', row); // row 为该客户的信息
                                    CreatePop.tip('添加成功');
                                    scope.clientBST.refresh({ clearFilter: true, page: 1 });
                                    form.reset();
                                    $('#j_company' + that.normalID).addClass('hide').find('input').attr('disabled', true);
                                }
                            });
                        }
                    });
                }
            });

            // 修改客户
            $('#j_client_table').on('click', '.j-handle-edit', function () {
                updateClientPop.show();
            });
            // 删除客户
            var delClient = new CreatePop({
                type: 'confirm',
                name: 'warning',
                blast: '.j-handle-del',
                delegate: '#j_client_table',
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该客户吗？',
                sureCallBack: function () {
                    var id = scope.clientBST.clickEve.row.id; // get client id from click event
                    service.client.deleteClient({ id: id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('删除成功！');
                            $.publish('BSTremove', [id]); // 从表格移除删除项
                        }                      
                    });
                }
            });

            // 导入客户
            var importClient = new CreatePop({
                type: 'prompt',
                name: 'import',
                title: '批量导入客户',
                blast: '#j_import_client',
                sureBtn: '上传',
                autoHide: false,
                afterMountCallBack: function () {
                    var that = this;
                    utils.importXlsxFile({
                        prefix: 'import-client',
                        url: service.client.uploadClientFile,   // 上传文件url
                        filter: { ext: "xlsx,xls", size: '1024kb' },    //文件过滤条件
                        tplUrl: '/dl/import_customer.xlsx',   // 设置模板下载的url
                        tplName: '导入客户模板.xlsx',         // 预设模板文件名
                        pop: this,                  // 当前pop实例
                        callback: function (res) { // 导入成功刷新列表
                            service.client.sendCustomerFileId({ id: res.data }).done(function (res) {
                                if (res.code === 200) {
                                    that.hide();
                                    CreatePop.tip('文件上传成功！');
                                    scope.clientBST.refresh({ clearFilter: true, page: 1 });
                                }
                            });
                        },
                    });
                }
            });
        },

        /******* 客户详情页 *****/
        detail: function (args) {
            // entry client detail page
            scope.pageView = 'detail';
            // 获取产品列表
            $.unsubscribe('getNecessaryOK');
            $.subscribe('getNecessaryOK', function () {
                if (scope.pageView !== 'detail') {
                    return;
                }
                var productList = scope.productTypeList.filter(function (ele, index, arr) {
                    return ele.status != 3;
                }, this);

                // 生成添加投资的下拉列表
                utils.repeatDOM(productList, 'product', addInvestmentPop.popID, true, 'replace');
            });
            getProducts();
            // 通过id获取详细的客户信息
            service.client.getClientDetail({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    // map data
                    scope.clientDetail = res.data.info;
                    // 客户基本信息
                    var obj = JSON.parse(JSON.stringify(res.data.info));
                    // 转换id为文字
                    obj.id_type = utils.parseIdType(obj.id_type);
                    obj.client_type = utils.parseClientType(obj.client_type);
                    // 绑定数据
                    utils.bindDataToDOM(obj, 'intro', '#j_client_intro');
                }
            });
            // 客户投资的产品列表
            scope.productBST = BST.extOption({
                bsTableId: '#j_product_table',
                url: service.client.getClientInvetment,
                queryParams: function (params) {
                    return $.extend(params, { id: args.params.id });
                },
            });

            /***** 模态框 *****/
            // 修改客户信息
            $('#j_edit_client').on('click', function () {
                updateClientPop.show();
            });
            // 添加报告
            var addInvestReportPop = new CreatePop({
                type: 'prompt',
                name: 'addInvestReport',
                title: '添加报告',
                blast: '.j-handle-add-report',
                delegate: '#j_product_table',
                sureBtn: '上传',
                autoHide: false,
                afterMountCallBack: function () {
                    var that = this;
                    // 上传报告
                    utils.importXlsxFile({
                        prefix: 'import-report',
                        url: service.client.uploadReportFile,      // 上传文件url
                        filter: { ext: "jpg,jpeg,png,bmp,xls,xlsx,xlsm,doc,docx,ppt,pptx,pdf,txt", size: '10240kb' },    //文件过滤条件
                        pop: that,                          // 当前pop实例
                        callback: function (res) {           // 上传成功回调
                            // 点击的列
                            var row = scope.productBST.clickEve.row;
                            // 勾选的报告类型
                            var reportType = that.$form[0].report_type.value

                            service.client.sendReportFileOpt({ file_id: res.data, id: row.id, report_type: reportType }).done(function (res) {
                                if (res.code === 200) {
                                    that.hide();
                                    CreatePop.tip('文件上传成功！');
                                    // 刷新列表
                                    row.report = 1;
                                    $.publish('BSTupdate', [row]);
                                }
                            });
                        }
                    });
                },
            });

            // 赎回
            var redeemPop = new CreatePop({
                type: 'confirm',
                name: 'info',
                title: '赎回',
                blast: '.j-handle-redeem',
                delegate: '#j_product_table',
                content: '您确定要赎回该产品么?',
                sureCallBack: function () {
                    service.client.redeem({ id: scope.productBST.clickEve.row.id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('赎回成功！');
                            var row = scope.productBST.clickEve.row;
                            row.operation = 1;
                            row.quit = res.data.time;
                            $.publish('BSTupdate', [row])
                        }
                    });
                }
            });
            // 转让
            var transferPop = new CreatePop({
                type: 'prompt',
                name: 'transfer',
                title: '选择转让客户',
                blast: '.j-handle-transfer',
                delegate: '#j_product_table',
                autoHide: false,
                afterMountCallBack: function () {
                    // jq DOM
                    var $originList = $('#j_origin_list'),
                        $searchList = $('#j_search_list'),
                        $searchInput = $('#j_search_input');
                    // variable
                    var originListId = '#j_origin_list',
                        searchListId = '#j_search_list',
                        that = this;
                    // 滚动加载更多客户
                    $originList.parent().off('scroll').on('scroll', function () {
                        if (this.scrollTop + $(this).height() + 30 > $(this).children().height()) {
                            // 上一次返回值小于limit，不再请求
                            if (scope.originClientOpt.limit !== scope.originClientLen) {
                                return;
                            }
                            utils.throttle(searchClient, 350, ['origin']);
                        }
                    });
                    // 滚动加载更多搜索
                    $searchList.parent().off('scroll').on('scroll', function () {
                        if (this.scrollTop + $(this).height() + 30 > $(this).children().height()) {
                            // 上一次返回值小于limit，不再请求
                            if (scope.searchClientOpt.limit !== scope.searchClientLen) {
                                return;
                            }
                            utils.throttle(searchClient, 350, ['search']);
                        }
                    });
                    // 显示搜索结果
                    $searchInput.off('input').on('input', function () {
                        if (this.value) {
                            // 键入关键字显示搜索结果
                            utils.throttle(showResult, 200);
                            scope.searchClientOpt.offset = 0; // 重设偏移量               
                            utils.throttle(searchClient, 350, ['search']); // 搜索并展示结果
                        } else {
                            // 清空搜索显示原始列表
                            utils.throttle(showOrigin, 200);
                        }
                    });
                    // 表单提交
                    this.$form.validate({
                        errorPlacement: function (err, ele) {
                            err.appendTo(ele.parents('form'));
                        },
                        submitHandler: function (form) {
                            if (!$.sendOK) { return; }
                            that.hide();
                            var $checked = that.$form.find('input:checked'), // 选中的radio
                                row = scope.productBST.clickEve.row, // 当前行
                                receiverName = $checked.next().text(), // 接受转让的名字
                                receiverId = $checked.val(), // 接受转让的id
                                rowId = row.id; // 当前行id
                            service.client.transfer({ id: rowId, to: receiverId }).done(function (res) {
                                if (res.code === 200) {
                                    CreatePop.tip('已成功转让给 <strong>' + receiverName + '</strong>'); // tip
                                    row.operation = 2;
                                    row.quit = res.data.time;
                                    $.publish('BSTupdate', [row]); // 更新列表
                                }
                            });
                        }
                    });
                    // map data
                    scope.searchClient = searchClient;
                    // 搜索
                    function searchClient(flag) {
                        if (flag === 'origin') {  // 不搜索，search字段为空
                            var opt = scope.originClientOpt;
                        } else if (flag === 'search') { // 搜索
                            scope.searchClientOpt.search = $searchInput.val().split('(')[0];
                            var opt = scope.searchClientOpt;
                        }
                        service.client.getClientNameList(opt).done(function (res) {
                            if (res.code === 200) {
                                // 生成dom
                                if (flag === 'origin') {
                                    utils.repeatDOM(res.data.list, 'client', originListId, true); // bind data
                                    scope.originClientOpt.offset += 15; // offset += 15
                                    scope.originClientLen = res.data.list.length; // save res length
                                }
                                if (flag === 'search') {
                                    utils.repeatDOM(res.data.list, 'client', searchListId, true); // bind data
                                    scope.searchClientOpt.offset += 15; // offset += 15
                                    scope.searchClientLen = res.data.list.length; // save res length
                                }
                            }
                        });
                    }
                    // 显示原始列表
                    function showOrigin() {
                        $originList.removeClass('hide');
                        $searchList.addClass('hide').find('input').attr('checked', false);
                    }
                    // 显示搜索结果
                    function showResult() {
                        $(searchListId).removeClass('hide').children(':gt(0)').remove();
                        $originList.addClass('hide').find('input').attr('checked', false);
                    }
                },
                beforeShowCallBack: function () {
                    // 获取转让列表
                    if (!scope.originClientOpt) { // 没有获取过
                        scope.originClientOpt = { offset: 0, limit: 15, search: '' };
                        scope.searchClientOpt = { offset: 0, limit: 15, search: '' };
                        scope.searchClient('origin');
                    }
                },
                sureCallBack: function () {
                    this.$form.submit();
                    // 确认转让
                }
            });
        },

        /******* 投资报告列表 *****/
        reportList: function (args) {
            scope.reportBST = BST.extOption({
                bsTableId: '#j_report_table',
                url: service.client.getReport,
                BSTSearchFromId: '#j_form_search',
                BSTSearchOBJPrefix: 'search'
                // queryParams: function (params) {
                //     return $.extend(params, { id: args.params.id });
                // }
            });

            // 恢复上一次的搜索条件
            // $.publish('BSTRstoreSearch');
        },
    }
});