define([
    'jquery',
    'service',
    'utils',
    'CreatePop',
    'BST',
    'plupload',
    'validate',
    'jedate'
], function ($, service, utils, CreatePop, BST, plupload) {
    'use strict';

    /*********** 模块公用函数 ***********/
    // 数据中心 map data
    var scope = {
        // 页面是否刷新
        refresh: true,
        // 标记当前页面视图名称
        pageView: 'list',
        // 客户列表
        clientList: [],
        // 产品列表
        productTypeList: [],
        // 证件类型
        IDCardTypeList: [],
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


    return {
        /* 添加投资 */
        add: function (args) {
            // entry add investment page
            scope.pageView = 'add';
            // jq DOM
            var $investDateInput = $('#j_invest_date'),     // 投资日期输入框
                $clientListWrapper = $('#j_list_wrapper'),  // 客户选择列表包裹
                $clientList = $('#j_select_list'),          // 客户选择列表
                $clientInfo = $('#j_client_info'),          // 右侧客户信息预览
                $searchInput = $('#j_client_name'),         // 搜索客户输入框
                $clientIdInput = $('#j_client_id'),         // 客户id输入框（隐藏）
                $addClientBtn = $('#j_show_addClient'),     // 添加客户按钮
                $investForm = $('#j_addInvest_form'),       // 添加投资阿牛
                $tiptext = $("#j_nodata_tip");              // 提示文字
            // variable
            var productListId = '#j_product_list',          // 产品select列表id
                selectListId = '#j_select_list',            // 额客户选择列表id
                searchInputId = 'j_client_name';            // 搜索客户输入框的id     
            // 获取产品列表
            service.investment.getProductTypeList({ offset: -1, status: 4 }).done(function (res) {
                if (res.code === 200) {
                    // 生成投资产品列表
                    utils.repeatDOM(res.data.list, 'product', productListId);
                }
            });

            // 时间插件
            $investDateInput.jeDate({
                format: 'YYYY-MM-DD',
                skinCell: 'jedatered',
                isTime: false,
                choosefun: function (obj) {
                    $investDateInput.trigger('blur');
                }
            });
            /* 选择列表 */
            // 从服务器获取输入的客户
            $searchInput.on('input', function () {
                utils.throttle(queryClient, 500, [$(this).val().trim()]);
                $(this).next().val('').siblings('span.has-error:last').text('请选择客户').show();
                // hide client detail info
                $clientInfo.addClass('hide');
            }).on('keyup', function (eve) {
                if (eve.keyCode === 13) {
                    queryClient($(this).val().trim());
                }
            }).on('focus', function () {
                $clientListWrapper.addClass('active');
            });
            // 选中列表项
            $clientList.on('click', 'li', function () {
                $clientListWrapper.removeClass('active');
                $searchInput.val($(this).text()).removeClass('has-error');
                $clientListWrapper.prev().val($(this).data('id')).removeClass('has-error')
                    .siblings('span.has-error').hide(); 
                // 显示客户详情
                $clientInfo.removeClass('hide');
                utils.bindDataToDOM(scope.clientList[$(this).index()-1], 'clientInfo', $clientInfo);
            });
            // 收起列表
            $(document).on('click', function (eve) {
                if (eve.target.id === searchInputId || eve.target.tagName === 'LI') {
                    return;
                } else {
                    $clientListWrapper.removeClass('active');
                }
            });
            // 显示添加客户
            $addClientBtn.on('click', function () {
                addClientPop.show();
            });
            // 函数 -- 向服务器查询输入的客户
            function queryClient(search) {
                if (search.trim()) {
                    var arr = search.split('(');
                    search = arr[0];
                    service.investment.getClientNameList({ search: search }).done(function (res) {
                        $clientList.children(':gt(0)').remove();
                        $clientListWrapper.addClass('active');
                        if (res.code === 200) { // 服务器存在查询的客户
                            scope.clientList = res.data.list; // 保存列表数据
                            if (res.data.list.length) {
                                utils.repeatDOM(res.data.list, 'clientList', selectListId, true);
                                $tiptext.addClass('hide');
                            } else { // 服务器不存在查询的客户
                                $tiptext.removeClass('hide');
                                $clientInfo.addClass('hide');
                            }
                        }
                    });
                }
            }

            // 添加客户的弹窗
            var addClientPop = new CreatePop({
                type: 'prompt',
                name: 'addClient',
                title: '添加客户',
                autoHide: false,
                afterMountCallBack: function () {
                    var that = this;
                    // 生成证件类型列表
                    scope.forClientPopEvent(that); // bind toggle company input event
                    $(that.formID).validate({ // validate form and submit
                        submitHandler: function (form) {
                            if (!$.sendOK) { return; }
                            var obj = utils.getFormData($(form).find('input[name="id"]').attr('disabled', true).end());
                            service.investment.addClient(obj).done(function (res) {
                                if (res.code === 200) {
                                    that.hide(0);
                                    $tiptext.addClass('hide');
                                    CreatePop.tip('添加成功');
                                    // set client name  client id
                                    $searchInput.val(obj.name + '(' + obj.phone + ')');
                                    $clientIdInput.val(res.data.id).valid();
                                    // show client detail info 
                                    var parseObj = {
                                        company_name: obj.company_name,
                                        credentials: obj.id_number,
                                        credentials_type: obj.id_type,
                                        mobile: obj.phone,
                                        name: obj.name,
                                        type: obj.client_type
                                    }
                                    utils.bindDataToDOM(parseObj, 'clientInfo', $clientInfo);
                                    $clientInfo.removeClass('hide');
                                    // reset form
                                    form.reset();
                                    $('#j_company' + that.normalID).addClass('hide').find('input').attr('disabled', true);
                                }
                            });
                        }
                    });
                },
                beforeShowCallBack: function () {
                    this.$form.find('input[name="name"]').val($searchInput.val());
                }
            });

            // 添加投资--表单提交
            $investForm.validate({
                submitHandler: function (form) { // 表单提交回调
                    if (!$.sendOK) { return; }
                    service.investment.addInvestment($(form).serialize()).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('投资成功！').afterHide(function() {
                                // 清空列表过滤条件
                                window.BSTdao.clearFilter();
                                setTimeout(function() {
                                    window.location.hash = '/invest/list';
                                }, 20);
                            });
                        }
                    });
                }
            });
            $investForm.on('submit', function () {
                $clientIdInput.val() ? null : $searchInput.addClass('has-error');
            });
        },

        /* 投资列表 */
        list: function (args) {
            // entry investment list page
            scope.pageView = 'list';
            // jq DOM
            var $startTimeInput = $('#j_start_time'),
                $endTimeInput = $('#j_end_time');


            // variable
            var tableID = '#j_invest_table',        // bs table的 id
                searchFormID = '#j_search_form',    // 搜索table的form的 id    
                importBtnId = '#j_import_invest';
            // 获取产品列表
            service.investment.getProductTypeList({ offset: -1 }).done(function (res) {
                // 绑定必要数据
                if (res.code === 200) {
                    scope.productTypeList = res.data.list;
                    // 生成搜索栏投资产品select
                    utils.repeatDOM(res.data.list, 'productType', searchFormID);
                    // 发布事件让 bs table还原上一次的搜索条件
                    $.publish('BSTRstoreSearch');
                }
            });
            // bs table 投资列表
            scope.investBST = BST.extOption({
                bsTableId: tableID,
                url: service.investment.getInvestList,
                BSTSearchFromId: searchFormID,
                BSTSearchOBJPrefix: 'search',
                responseHandler: function (res) {
                    var obj = {};
                    if (res.code === 200) {
                        res.data.list.forEach(function (ele, index, arr) {
                            scope.productTypeList.forEach(function (e, i, a) {
                                ele.product == e.id && (ele.product = e.title);
                            }, this);
                        }, this);
                        obj.rows = res.data.list || [];
                        obj.total = res.data.total || 0;
                        scope.investBST.totalPages = obj.total;
                        scope.investBST.dataLen = obj.rows.length;
                    } else {
                        CreatePop.warning(res.msg).sure(function () { 
                            if (res.code === 402) {
                                window.sessionStorage.setItem('_history_hash', window.location.hash);
                                window.location = '/login';
                            }
                        });
                    }
                    return obj;
                },
            });


            // 时间输入框
            var startT = {
                onClose: true,
                format: 'YYYY-MM-DD',
                skinCell: 'jedatered',
                isTime: false,
                // maxDate: $.nowDate({ DD: 0 }), //最大日期
                okfun: function(obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    BSTSearch(tableID);
                },
                choosefun: function (obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    BSTSearch(tableID);
                },
                clearfun: function (elem, val) {
                    BSTSearch(tableID);
                },
            };
            var endT = {
                onClose: true,
                format: 'YYYY-MM-DD',
                skinCell: 'jedatered',
                isTime: false,
                // maxDate: $.nowDate({ DD: 0 }), //最大日期
                okfun: function(obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    BSTSearch(tableID);
                },
                choosefun: function (obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    BSTSearch(tableID);
                },
                clearfun: function (elem, val) {
                    BSTSearch(tableID);
                },
            };
            // 投资日期时间输入框
            $startTimeInput.jeDate(startT);
            $endTimeInput.jeDate(endT);


            // 删除一条投资
            var delInvestment = new CreatePop({
                type: 'confirm',
                name: 'warning',
                title: '警告',
                blast: '.j-handle-del',
                delegate: tableID,
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该投资吗？',
                sureCallBack: function () {
                    var id = scope.investBST.clickEve.row.id; // get client id from click event
                    service.investment.deleteInvestment({ id: id }).done(function (res) {
                        res.code === 200 && $.publish('BSTremove', [id]); // 从表格移除删除项
                    });
                }
            });
            // 导入投资
            var importInvest = new CreatePop({
                type: 'prompt',
                name: 'import',
                title: '批量导入投资',
                blast: importBtnId,
                autoHide: false,
                sureBtn: '上传',
                afterMountCallBack: function () {
                    var that = this;
                    utils.importXlsxFile({
                        prefix: 'import-investment',
                        url: service.investment.importInvestment,   // 上传文件url
                        filter: { ext: "xlsx,xls", size: '1024kb' },    //文件过滤条件
                        tplUrl: '/dl/import_investment.xlsx',   // 设置模板下载的url
                        tplName: '导入投资模板.xlsx',         // 预设模板文件名
                        pop: this,                  // 当前pop实例
                        callback: function (res) { // 导入成功回调，刷新列表
                            service.investment.sendInvestFileId({ id: res.data }).done(function (res) {
                                if (res.code === 200) {
                                    that.hide();
                                    CreatePop.tip('文件上传成功！');
                                    scope.investBST.refresh({ clearFilter: true, page: 1 });
                                }
                            });
                        },
                    });
                }
            });
        },

        /* 投资详情 */
        detail: function (args) {
            // entry investment detail page
            scope.pageView = 'detail';
            // 获取投资详情
            service.investment.getInvestDetail({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    var obj = res.data.info;
                    // 把id转换成对应的文字
                    for (var key in obj) {
                        switch (key) {
                            case 'client_type':
                                obj[key] = utils.parseClientType(String(obj[key]));
                                break;
                            case 'id_type':
                                obj[key] = utils.parseIdType(String(obj[key]));
                                break;
                            case 'status':
                                obj[key] = utils.parseProductStatus(String(obj[key]));
                                break;
                        }
                    }
                    // 绑定数据
                    utils.bindDataToDOM(obj, 'obj', '#j_invest_detail');
                }
            });
        }
    }
});