define([
    'jquery',
    'DAO',
    'CreatePop',
    'utils',
    'bootstrapTable',
], function ($, DAO, CreatePop, utils) {
    'use strict';

    var BST = {
        /**
         * 自定义bstable的参数，传入opt，返回新的参数
         * eg----------------------------------------------------------
         *  js: var bstable = BST.extOption({
         *          bsTableId: '#j_xxx_xx',
         *          url: service.xx.xxx(),
         *          BSTSearchFromId: '#j_form_search',
         *          BSTSearchOBJPrefix: 'search', 
         *          responseHandler: function(res) {}
         *          ...
         *      });
         *      
         *      $.publish('BSTadd', obj); // 插入新数据
         *      $.publish('BSTremove', [id]); // 移除数据
         *      $.publish('BSTupdate', [obj]); // 更新数据
         *      $.publish('BSTRstoreSearch'); // 刷新页面时恢复搜索状态
         *      其他用法    
         *      从实例中获取该实例对应的dao，该dao用于保存该实例的请求状态
         *      bstable.bstDAO.get('key'), bstable.bstDAO.set('key', 'value'),
         *      从实例中获取点击表格事件时产生的参数，该参数是被点击列的数据对象
         *      bstalbe.clickEve.params // {clientType: "机构客户", companyName: "fsdfsfssdf", name: "dsfsdfsfs"}
         *      bstalbe.clickEve.$element  // $(<tr> <td>xxx</td> </tr>)   jquery对象
         * ----------------------------------------------------------eg
         * 说明：直接传入bootstrap table 的其他合法参数均可。
         * 
         * @param       {String}        bsTableId           // 表格的id   [必要]（jquery选择器）
         * @param       {String}        url                 // 请求地址   [必要]
         * @param       {String}        BSTSearchFromId     // 顶部搜索栏表单id [必要]（jquery选择器）
         * @param       {String}        BSTSearchOBJPrefix  // 顶部搜索栏表单绑定数据的前缀 [必要] 
         * @param       {Object}        queryObj            // 额外的查询参数
         * @param       {Function}      responseHandler     // 处理服务器返回数据 [可选]
         * @param       {Function}      successCallback     // 服务器成功返回数据后回调 [可选]
         * @returns     {Object}        
         */
        extOption: function (opt) {
            if (!opt.bsTableId) {
                return console.error('参数错误，无法获取表格数据！');
            }
            // 以url作为dao的前缀，区分不同表格的 状态数据
            var dao = new DAO('BST');
            // 表格向外传参数
            var bstable = {};
            var bstOpt = {
                url: '',
                method: 'post',
                contentType: "application/x-www-form-urlencoded",	// 如果是post必须定义
                dataType: "json",
                striped: false,                     // 是否显示行间隔色
                // sortName: 'name',                // 排序 
                // sortOrder: 'asc',                // 升序[desc降序]
                pagination: true,                   // 分页
                pageNumber: 1,                      // 初始化加载第一页，默认第一页
                pageSize: 10,                       // 每页的条数
                // pageList: [2, 25, 50, 100],      // 可供选择的pageSize的大小
                // search: false,                   // 显示搜索框
                uniqueId: 'id',                     // 用一个field作为区分的ID
                sidePagination: "server",           // 服务端处理分页[client]
                paginationLoop: false,              // 首位页循环切换
                paginationPreText: '上一页',         // 自定义文字
                paginationNextText: '下一页',        // 自定义文字
                // 修改查询字符串
                queryParams: function (params) {
                    // 判断是否是刷新页面
                    if (dao.get('refresh').value) {
                        // 取出上一次的查询参数
                        var queryObj = JSON.parse(dao.get('searchArgs').value);
                        queryObj = queryObj && queryObj[window.location.hash];
                        // 刷新页面, bstable 从 localStorage 中取出查询参数来扩充 原请求参数
                        params = $.extend(params, queryObj);
                        // 设置激历史记录的 活页码
                        var _pageNumber = JSON.parse(dao.get('pageNumber').value);
                        if (_pageNumber && _pageNumber[window.location.hash]) {
                            this.pageNumber = parseInt( _pageNumber[window.location.hash]);
                        } else {
                            this.pageNumber = 1;
                        }
                         
                        // 还原搜搜表单的数据
                        if (opt.BSTSearchFromId) {
                            $.unsubscribe('BSTRstoreSearch');
                            $.subscribe('BSTRstoreSearch', function (e) {
                                utils.setFormData(opt.BSTSearchFromId, queryObj);
                            });
                        }
                    } else {
                        // 使用传入的请求参数来扩充原有参数
                        if (opt.BSTSearchFromId) {
                            params = $.extend(params, utils.getDataFromDOM(opt.BSTSearchOBJPrefix, opt.BSTSearchFromId));
                        }
                        // 保存请求参数到localStorage
                        var searchArgs = {},
                            pageArgs = {};
                        searchArgs[window.location.hash] = params;
                        dao.set('searchArgs', JSON.stringify(searchArgs));
                        // 保存当前页码
                        pageArgs[window.location.hash] = this.pageNumber;
                        dao.set('pageNumber', JSON.stringify(pageArgs));

                    }
                    // console.log(this.pageNumber)
                    bstable.pageNumber = this.pageNumber;
                    $.extend(params, opt.queryObj); // 额外的查询参数
                    return params;
                },
                // 处理服务器返回数据
                responseHandler: function (res) {
                    $.sendOK = true;
                    var obj = {};
                    if (res.code === 200) {
                        // 回调
                        opt.successCallback && opt.successCallback(res.data);
                        if (res.data.list instanceof Array) {
                            // 当前页码大于1并且请求无数据，则请求上一页
                            if (res.data.list.length === 0 && bstable.pageNumber > 1) {
                                $(opt.bsTableId).bootstrapTable('refresh', { pageNumber: bstable.pageNumber - 1 });
                            }
                            // 扩展obj
                            $.extend(obj, { rows: res.data.list, total: res.data.total });
                            // 记录当前总条数 
                            bstable.totalPages = obj.total;
                            // 记录当前渲染条数
                            bstable.dataLen = obj.rows.length;
                        } else {
                            console.error('返回值不是一个数组');
                        }
                    } else {
                        CreatePop.warning(res.msg)
                        .afterHide(function () { 
                            if (res.code === 402) {
                                window.sessionStorage.setItem('_history_hash', window.location.hash);
                                window.location = '/login';
                            }
                        });
                    }
                    return obj;
                },
                // 分页详细信息
                formatShowingRows: function (from, to, total) {
                    return '显示 ' + from + ' 条至 ' + to + ' 条，一共 ' + total + ' 条';
                },
                // 搜索无结果的提示
                formatNoMatches: function () { return '暂无数据'; },
                // 加载提示文字
                formatLoadingMessage: function () { return ''; },
                // pagelist可选择分页大小，设为隐藏
                formatRecordsPerPage: function () { return ''; },
                // 加载失败的回调
                onLoadError: function (status, res) {
                    CreatePop.error(res.statusText);
                    console.error(res.statusText + 'bstable resource !');
                },
                // onPreBody: function(data) {
                //     console.log(data)
                // },
                // 点击单元格事件
                onClickRow: function (row, $element) {
                    // 向外传参 obj
                    var obj = { row: row, $element: $element }
                    bstable.clickEve = obj;
                },
                // onClickCell: function (field, value, row, $element) {
                //     // 向外传参 obj
                //   console.log(field, value, row, $element)
                // },
                // 加载成功的回调
                // onLoadSuccess: function (data) {
                //     console.log(data)
                //     console.log(this)
                // },
            };
            // 扩展表格参数
            $.extend(bstOpt, opt);
            // 生成表格之前  标记刷新状态开始
            dao.set('refresh', true);
            // 生成表格
            $(opt.bsTableId).bootstrapTable(bstOpt);
            // 生成表格后 标记刷新状态结束
            dao.set('refresh', false);
            // 向外发布该table的dao
            bstable.bstDAO = dao;
            // 刷新 页码，是否清空过滤条件
            bstable.refresh = function (param) { 
                if (param.clearFilter && opt.BSTSearchFromId) {
                    $(opt.BSTSearchFromId)[0].reset();
                    window.BSTdao.clearFilter();
                } 
                $(opt.bsTableId).bootstrapTable('refresh', { pageNumber: param.page || 1 }); 
            }
            // 插入新数据
            $.unsubscribe('BSTadd');
            $.subscribe('BSTadd', function (e, row) {
                $(opt.bsTableId).bootstrapTable('prepend', row);
                bstable.totalPages++;
                bstable.dataLen++;
                $(opt.bsTableId).bootstrapTable('load', { to: bstable.dataLen, total: bstable.totalPages });
            });
            // 移除数据
            $.unsubscribe('BSTremove');
            $.subscribe('BSTremove', function (e, id) {
                $(opt.bsTableId).bootstrapTable('removeByUniqueId', id);
                bstable.totalPages--;
                bstable.dataLen--;
                if (bstable.dataLen <= 0) {
                    $(opt.bsTableId).bootstrapTable('refresh', { pageNumber: bstable.pageNumber - 1 });
                }
                $(opt.bsTableId).bootstrapTable('load', { total: bstable.totalPages });
            });
            
            // 更新数据
            $.unsubscribe('BSTupdate');
            $.subscribe('BSTupdate', function (e, row) {
                $(opt.bsTableId).bootstrapTable('updateByUniqueId', { id: row.id, row: row });
            });
            // 修复点击激活的页码跳转到 /#/ 的问题
            $('.fixed-table-pagination').on('click', 'a', function() {
                return false;
            });
            // 修复禁用的页面按钮可被点击的bug
            $('.fixed-table-pagination').on('click', 'li.disabled>a', function(e) {
                e.preventDefault();
                return false;
            });
            $('.fixed-table-pagination').on('click', 'li.disabled', function(e) {
                e.preventDefault();
                return false;
            });
            return bstable;
        },
        /**
         * 渲染操作列的DOM
         * eg---------------------------------------
         * <thead>
         *      <th data-field="name">名称</th>
         *      <th data-field="phone">电话号码</th>
         *      <th data-field="id" data-formatter="BSTFormatHandleRow.client">操作</th>
         * </thead>
         * ---------------------------------------eg
         */
        formatHandleRow: {
            // 客户列表
            client: {
                type: function (value, row, index) {
                    return utils.parseClientType(String(value));
                },
                credentialsType: function (value, row, index) {
                    return utils.parseIdType(String(value));
                },
                handle: function (value, row, index) {
                    return '<a class="handle" href="#/client/detail/' + row.id + '">查看</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle j-handle-edit" href="javascript:">修改</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle j-handle-invest" href="javascript:">投资</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                },
                reportType: function(value, row, index) {
                    return value && ['','投后报告', '合规文件', '基金合同'][value]
                } 
            },

            // 客户详情产品列表
            productForClient: {
                name: function (value, row, index) {
                    return '<a class="handle" href="#/product/detail/' + row.product_id + '">' + value + '</a>';
                },
                report: function (value, row, index) {
                    if (Number(row.report) !== 0) {
                        return '<a class="handle" href="#/client/invest/report/' + row.id + '">查看报告</a>';
                    } else {
                        return '<a class="handle disabled">暂无报告</a>';
                    }
                },
                status: function (value, row, index) {
                    return utils.parseProductStatus(value);
                },
                handle: function (value, row, index) {
                    var redeem = Number(row.operation) === 1,
                        transfer = Number(row.operation) === 2;
                    if (Number(row.status) === 3) {
                        return '<a class="handle disabled" href="javascript:">' + (redeem ? '已赎回' : '赎　回') + '</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled" href="javascript:">' + (transfer ? '已转让' : '转　让') + '</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled" href="javascript:">添加报告</a>';
                    } else {
                        return '<a class="handle ' + (redeem || transfer ? 'disabled' : 'j-handle-redeem') + '" href="javascript:">' + (redeem ? '已赎回' : '赎　回') + '</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle ' + (redeem || transfer ? 'disabled' : 'j-handle-transfer') + '" href="javascript:">' + (transfer ? '已转让' : '转　让') + '</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle ' + (redeem || transfer ? 'disabled' : 'j-handle-add-report') + '" href="javascript:">添加报告</a>';
                    }
                },
                reportList: function (value, row, index) {
                    return '<a class="handle" href="' + row.url + '" download="' + row.time + '--投资报告' + row.ext + '">下载</a>';
                },
            },

            // 产品管理列表
            product: {
                status: function (value, row, index) {
                    return utils.parseProductStatus(value);
                },
                handle: function (value, row, index) {
                    return '<a class="handle" href="#/product/detail/' + row.id + '">查看</a>'
                        + ' <span class="split">|</span>'
                        + '<a class="handle j-handle-edit" href="#/product/edit/' + row.id + '">修改</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle del ' + (row.status != 3 ? 'disabled' : 'j-handle-del') + '" href="javascript:">删除</a>';
                },
            },

            // 新闻列表
            news: {
                type: function(value, row, index) {
                    switch (Number(value)) {
                      case 1:
                        return '项目动态';
                      case 2:
                        return '精选文摘';
                      case 3:
                        return '公司动态';
                    }
                },
                handle: function (value, row, index) {
                    return '<a class="handle" href="#/news/detail/' + row.id + '">查看</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                },
            },
            // 通知列表
            notice: {
                status: function (value, row, index) {
                    switch (String(value)) {
                        case '1':
                            return '已发送';
                        case '2':
                            return '待发送';
                        case '3':
                            return '已撤销'
                    }
                },
                handle: function (value, row, index) {
                    return '<a class="handle" href="#/notice/detail/' + row.id + '">查看</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle ' + (String(row.status) === '2' ? 'j-handle-revoke' : 'disabled') + '" href="javascript:">'+ (String(row.status) === '3' ? '已撤销' : '撤　销') +'</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                }
            },
            // 投票列表
            vote: {
                status: function(value, row, index) {
                    switch(Number(value)) {
                        case 1:
                            return '正常';
                        case 2:
                            return '结束';
                        case 3:
                            return '暂停';
                    }
                },
                handle: function (value, row, index) {
                    if (String(row.is_publish) === '1') {
                        var isFinish = Number(row.status === 2);
                        return '<a class="handle disabled">预览</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">编辑</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">选项</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/vote/setting/' + row.id + '">设置</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">发布</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/vote/statistics/' + row.id + '">统计</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                    } else {
                        return '<a class="handle j-handle-preview" href="javascript:">预览</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/vote/add/' + row.id + '">编辑</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/vote/option/' + row.id + '">选项</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/vote/setting/' + row.id + '">设置</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle j-handle-publish" href="javascript:">发布</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">统计</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                    }
                },
            },
            // 调研列表
            survey: {
                status: function(value, row, index) {
                    switch(Number(value)) {
                        case 1:
                            return '正常';
                        case 2:
                            return '结束';
                    }
                },
                paperNum: function (value, row, index) {
                    var isPublish = Number(row.is_publish);
                    if (isPublish) {
                        return '<a class="handle" href="#/survey/paper/list/' + row.id + '">' + row.paper_num + '</a>';
                    } else {
                        return '<a class="handle disabled">-</a>';
                    }
                },
                handle: function (value, row, index) {
                    if (Number(row.status) === 2) {
                        return '<a class="handle disabled">预览</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">编辑</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle disabled">选项</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle ' + (Number(row.status) !== 0 ? 'disabled' : 'j-handle-publish') + '" href="javascript:">发布</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle" href="#/survey/statistics/' + row.id + '">统计</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                    } else {
                        var isPublish = Number(row.is_publish);
                            // isFinish = Number(row.status === 2);
                        return '<a class="handle '+ (isPublish ? 'disabled' : 'j-handle-preview') +'" href="javascript:">预览</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle '+ (isPublish ? 'disabled' : '" href="#/survey/add/' + row.id) + '">编辑</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle '+ (isPublish ? 'disabled' : '" href="#/survey/option/' + row.id) + '">选项</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle ' + (isPublish ? 'disabled' : 'j-handle-publish') + '" href="javascript:">发布</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle '+ (!isPublish ? 'disabled': '" href="#/survey/statistics/' + row.id ) +'">统计</a>'
                            + '<span class="split">|</span>'
                            + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                    }
                },
                paper: {
                    handle: function (value, row, index) {
                        var hash = window.location.hash,
                            i = hash.lastIndexOf('/'),
                            surveyId = hash.substring(i + 1);
                        return '<a class="handle" href="#/survey/paper/detail/' + surveyId + '/' + row.id + '/' + row.memid + '">查看</a>'
                    }
                }
            },
            // 投资列表
            invest: {
                client: function (value, row, index) {
                    return utils.parseClientType(String(value));
                },
                handle: function (value, row, index) {
                    return '<a class="handle" href="#/invest/detail/' + row.id + '">查看</a>'
                        + '<span class="split">|</span>'
                        + '<a class="handle del j-handle-del" href="javascript:">删除</a>';
                }
            }
        },
        /**
         * bootstrap table 搜索
         * eg----------------------------
         * <form name="search" id="j_search_form">
         *      <input type="text" name="search" oninput="BSTSearch('#j_table_id', 600)"
         * </form>
         * ----------------------------eg
         * @param       {String}        id          bootstrap tabel的id  [必要]（jquery选择器）
         * @param       {Number}        delay       延迟执行搜索的时间     [可选]（影响搜索的速度和性能）
         */
        search: function (id) {
            $(id).bootstrapTable('refresh', { pageNumber: 1 });
        }
    };
    // 暴露全局方法

    window.BSTFormatHandleRow = BST.formatHandleRow;
    window.BSTSearch = function (id, delay) {
        utils.throttle(BST.search, delay, [id]);
    }
    return BST;


});