define([
    'jquery',
    'service',
    'utils',
    'CreatePop',
    'DAO',
    'BST',
    'validate',
    'jedate',
], function ($, service, utils, CreatePop, DAO, BST) {
    'use strict';
    /*********** 模块公用函数 ***********/
    // 数据中心 map data
    var scope = {
        // 产品详情
        productDetail: {},
    };
    return {
        /* 新增产品 */
        add: function (args) {
            // jq DOM
            var $establishInput = $('#j_establish_time'),   // 成立日期
                $deadlineInput = $('#j_deadline_time'),     // 到期日期
                $form = $('#j_add_product_form');           // 添加产品的表单

            // 时间选择插件
            // 产品成立日期
            $establishInput.jeDate({
                format: 'YYYY-MM-DD',
                skinCell: 'jedatered',
                isTime: false,
                okfun: function() {
                    $establishInput.trigger('blur'); 
                },
                choosefun: function (obj) {
                    $establishInput.trigger('blur');
                }
            });
            // 添加产品，表单提交
            $form.validate({
                submitHandler: function (form) {
                    if (!$.sendOK) { return; }
                    var obj = $(form).serialize();
                    service.product.addProdcut(obj).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('添加成功').afterHide(function() {
                                // 清空列表过滤条件
                                window.BSTdao.clearFilter();
                                setTimeout(function() {
                                    window.location.hash = '/product/list';
                                }, 20);
                            });;
                            // $(form)[0].reset();
                        }
                    });
                }
            });
        },
        /* 产品列表 */
        list: function (args) {
            // bs table
            var productBST = BST.extOption({
                bsTableId: '#j_product_table',
                url: service.product.getProductList,
                BSTSearchFromId: '#j_form_search',
                BSTSearchOBJPrefix: 'search',
            });
            // 恢复上一次的搜索条件
            $.publish('BSTRstoreSearch');
            // 删除产品
            var delProduct = new CreatePop({
                type: 'confirm',
                name: 'warning',
                blast: '.j-handle-del',
                delegate: '#j_product_table',
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该产品吗？',
                sureCallBack: function () {
                    var id = productBST.clickEve.row.id; // get product id from click event
                    service.product.delProduct({ id: id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('删除成功！');
                            $.publish('BSTremove', [id]); // 从表格移除删除项
                        } 
                    });
                }
            });
        },
        edit: function (args) {

            // jq DOM
            var $establishInput = $('#j_establish_time'),   // 时间输入框
                $form = $('#j_edit_product_form');         // 表单
            // variable
            var formId = '#j_edit_product_form';
            // 获取产品信息
            service.product.getProductDetail({ id: args.params.id }).done(function (res) {
                res.code === 200 && utils.setFormData(formId, res.data)
            });

            // 日期输入框
            $establishInput.jeDate({
                format: 'YYYY-MM-DD',
                skinCell: "jedatered",
                isTime: false,
                okfun: function() {
                    $establishInput.trigger('blur');
                },
                choosefun: function (obj) {
                    $establishInput.trigger('blur');
                }
            });
            // 更新产品信息
            $form.validate({
                submitHandler: function (form) {
                    if (!$.sendOK) { return; }
                    var obj = $(form).serialize();
                    // $.extend(obj, {name: id, value: args.params.id});
                    service.product.updateProduct(obj).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('修改成功').afterHide(function() {
                                window.location.hash = '/product/list';
                            });
                            // utils.setFormData(formId, res.json);
                        }
                    });
                }
            });
        },
        detail: function (args) {
            // jq DOM
            var $btn = $('#j_edit_product');    // 修改按钮
            // variable
            var scopeId = '#j_product_detail',  // bind data context
                link = '#/product/edit/' + args.params.id;       // 跳转到修改路由

            // 获取产品详情
            service.product.getProductDetail({ id: args.params.id }).done(function (res) {
                // 绑定数据
                if (res.code === 200) {
                    res.data.status = utils.parseProductStatus(res.data.status);
                    utils.bindDataToDOM(res.data, 'product', '#j_product_scope');
                    // 设置返回键的url地址
                    $btn.attr('href', link);
                    scope.productDetail = res.data; // map data
                }
            });
        }
    }
});


