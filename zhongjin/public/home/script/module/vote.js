
define([
    'jquery',
    'service',
    'utils',
    'CreatePop',
    'DAO',
    'BST',
    'tree',
    'validate',
    'jedate',
    'xiumiV5'
], function ($, service, utils, CreatePop, DAO, BST, tree) {
    'use strict';
    /*********** 模块公用函数 ***********/
    // 数据中心 map data
    var scope = {
        // 页面是否刷新
        refresh: true,
        // 在编辑的投票的id
        newVoteId: 0,
    };

    // 确认
    scope.confirmPop = new CreatePop({
        type: 'confirm',
        name: 'info',
        // autoHide: false,
        content: '是否保存当前表单？',
    });
    return {
        /* 投票内容 */
        add: function (args) {
            // entry add vote page
            scope.follow = 'next';
            // jq DOM
            var $coverIdInput = $('#j_cover_input'),    // 封面id输入框
                $previewImg = $('#j_preview_img'),      // 选择文件下方的预览 img
                $form = $('#j_vote_form'),              // 新增新闻的表单 form
                $selectObjNameInput = $('#j_selected_member'), // 选择对象名字输入框
                $selectSendObjBtn = $('#j_show_selectOBJ'), // 选择发送对象按钮
                $saveBtn = $('#j_save_btn');                // 编辑时显示保存按钮

            // variable
            var formID = '#j_vote_form',                // 新增新闻的表单的id
                UETextareaId = 'j_u_editor';            // 富文本 textarea id  
         
            // 富文本编辑器
            var ue = utils.initUE(UE, UETextareaId, null, true);

            // 绑定表单数据，编辑投票内容
            if (Number(args.params.id)) {
                $saveBtn.removeClass('hide');
                service.vote.getVoteContent({ id: args.params.id }).done(function (res) {
                    if (res.code === 200) {
                        var detail = res.data.detail;
                        // 设置表单数据
                        utils.setFormData(formID, detail);
                        // set cover
                        detail.cover && $previewImg.attr('src', detail.cover).removeClass('hide');
                        // 设置富文本数据
                        ue.ready(function () {
                            detail.description && ue.setContent(detail.description)
                        });
                        // 设置选择发送对象数据
                        if (detail.send_type === 1){
                            scope.send_obj = $.extend({ category: [], member: [] }, detail.send_obj);
                            tree.setData(scope.send_obj);
                            $selectObjNameInput.val(detail.names);
                        } 
                    }
                });
            }

            // 选择发送对象
            tree.init(function(result) {
                $selectObjNameInput.val(result.names);
                delete result.names;
                scope.send_obj = result;
            });
            // scope.send_obj && tree.setData(scope.send_obj); // set tree's data
            $selectSendObjBtn.on('click', function() {
                tree.showPop();
            });

            // 上传封面
            utils.uploadFile({
                prefix: 'vote-cover',
                url: service.vote.uploadCover,   // 上传文件url
                btn: 'j_browse_btn',        // 浏览按钮
                fileNamePlace: 'j_file_name_place', // 文件名称放置位置
                fileNameInit: '(推荐尺寸：900*500,图片大小不超过64K)', // 初始文件名称
                container: 'j_upload_container', // 
                previewSelector: ['#j_preview_img'],
                filter: {
                    size: '2mb',
                    ext: "png,jpg,jpeg,bmp"
                },
                callback: function (res) { 
                    if (res.code === 200) {
                        $coverIdInput.val(res.data).valid();
                    } else {
                        CreatePop.warning(res.msg);
                    }
                },
            });

            // 富文本不能为空
            $form.on('submit', function () { !ue.getContent() && $('.edui-editor').addClass('has-error') });
            $saveBtn.on('click', function() {
                scope.follow = 'save';
                $(formID).submit();
            });
            // 投票内容表单提交
            $form.validate({
                errorPlacement: function (err, ele) {
                    err.appendTo(ele.parents('.form-group'));
                },
                submitHandler: function (form) {
                    // 保存当前表单
                    var obj = $.extend(utils.getFormData($(form)), { send_type: 0 });
                    // 发送对象
                    if ($('[name="send_type"]:checked').val() === '1') {
                        if (scope.send_obj)  {
                            if (scope.send_obj.category[0] !== -1) {
                                $.extend(obj, {send_type: 1,  send_obj: scope.send_obj });
                            }
                        } else {
                            CreatePop.tip('请选择发送对象');
                            return;
                        }
                    }
                    // 结束语
                    obj.epilogue || (obj.epilogue = '您的答案已提交，感谢您的参与！');
                    // 当前为编辑
                    if (!$.sendOK) { return; }
                    if (Number(args.params.id)) {
                        $.extend(obj, { id: args.params.id });
                        service.vote.updateVoteContent(obj).done(function (res) {
                            if (res.code === 200) {
                                if (scope.follow === 'save') {
                                    window.location.hash = '/vote/list'; // 跳转到列表
                                } else {
                                    window.location.hash = '/vote/option/' + args.params.id; // 跳转下一步
                                }
                            }
                        });
                    } else { // 当前为新增
                        service.vote.addVoteContent(obj).done(function (res) {
                            if (res.code === 200) {
                                window.location.hash = '/vote/option/' + res.data.id; // 跳转下一步
                            }
                        });
                    }
                }
            });
        },
        /* 投票选项 */
        option: function (args) {
            // entry vote option page
            // 当前选项数量 
            scope.optionNum = 2;
            // 跳转流程走向
            scope.follow = 'next';
            scope.hasOptionDetail = false; // 是否返回选项数据，标记是编辑还是新增
            // jq DOM
            var $optionWrapper = $('#j_option_wrapper'),// 操作选项的按钮的容器
                $prevBtn = $('#j_prev_btn'),            // 提交表单按钮
                $saveBtn = $('#j_save_btn'),                // 编辑时显示保存按钮
                $multipleInput = $('#multiple_ck'),     // 多选按钮
                $singleInput = $('#single_ck'),         // 单选按钮
                $requiredInput = $('#j-required'),      // 必答题
                $minChecked = $('#j_min_ck'),           // 最少选择多少项 select
                $maxChecked = $('#j_max_ck'),           // 最多选择多少项 select   
                $form = $('#j_vote_form');              // 表单

            // variable
            var formID = '#j_vote_form';                // 表单id 
            // 绑定表单数据，编辑投票选项
            service.vote.getVoteOption({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    setLimit(res.data.detail['option'] && res.data.detail['option'].length);
                    // 设置表单数据
                    if (Object.keys(res.data.detail).length) {
                        // flag
                        scope.hasOptionDetail = true;
                        $saveBtn.removeClass('hide');
                        var question = res.data.detail;
                        scope.questionId = question.question_id; // save question id
                        // 多选
                        question.type == 1 && $singleInput.trigger('click');
                        // question.required == 1 && $requiredInput.trigger('click');
                        var questionLen = question.option.length;
                        // append dom
                        for (var i = 2; i < questionLen; i++) {
                            // textarea
                            $optionWrapper.find('.j-handle.j-add').last().trigger('click');
                        }
                        // 限制选项
                        if (question.type == 2) {
                                $multipleInput.trigger('click');                           
                                $minChecked.val(question.least).trigger('change');
                                $maxChecked.val(question.most);
                        }
                        // set option
                        $optionWrapper.find('textarea').each(function(index, ele) {
                            ele.value = question.option[index];
                        });
                    }
                }
            });
            $saveBtn.on('click', function() {
                scope.follow = 'save';
                $(formID).submit();
            });
            // 上一步，提示保存
            $prevBtn.on('click', function () {
                scope.confirmPop.show()
                    .sure(function () {         // 保存表单，跳转到上一步
                        scope.follow = 'prev';  // 标记跳转为上一步
                        $(formID).submit();     // 触发表单提交而已
                    }).cancel(function () {     // 直接跳转到上一步
                        window.location.hash = '/vote/add/' + args.params.id;
                    });
            });
            // 下一步，投票选项表单提交 
            $form.validate({
                submitHandler: function (form) {
                    // 获取表单数据
                    if (!$.sendOK) { return; }
                    var obj = $.extend(utils.getFormData($form), { id: args.params.id });
                    var whole = obj.option.every(function(item) { return item.trim() });
                    if (!whole) {
                        return CreatePop.tip('请把选项填写完整');
                    }
                    if (scope.hasOptionDetail) { // 更新操作
                        $.extend(obj, { question_id: scope.questionId });
                        service.vote.updateVoteOption(obj).done(function (res) {
                            if (res.code === 200) {
                                // 跳转页面
                                redirectTo();
                            }
                        });
                    } else { // 新增操作
                        service.vote.addVoteOption(obj).done(function(res) {
                            if (res.code === 200) {
                                // 跳转页面
                                redirectTo(0);
                            }
                        });
                    }
                }
            });

            // 限制最大最小选项
            $minChecked.on('change', function() {
                // 设置最小选择和最大选择数量 
                var eqIndex = this.value - 1;
                $maxChecked.children().removeClass('hide').filter(':lt(' + eqIndex + ')').addClass('hide').end()
                    .each(function (index, ele) {
                        if (index === eqIndex) {
                            ele.selected = 'selected';
                        } else {
                            ele.removeAttribute('selected');
                        }
                    });
            });

            // 选项设置
            $optionWrapper.on('click', '.j-handle', function (eve) {
                if ($(this).hasClass('j-del')) {        // 删除选项
                    delOption(this);
                    return false;
                } else if ($(this).hasClass('j-add')) { // 增加选项
                    addOption(this);
                    return false;
                } else if ($(this).hasClass('j-up')) { // 向上移动
                    changPotion(this, 'up');
                    return false;
                } else if ($(this).hasClass('j-down')) { // 向下移动
                    changPotion(this, 'down');
                    return false;
                }
            });
            // 跳转页面 flag :0表示新增
            function redirectTo(flag) {
                if (scope.follow === 'prev') {
                    window.location.hash = '/vote/add/' + args.params.id;
                } else if (scope.follow === 'next') {
                    if (flag === 0) {
                        scope.addVoteOperation = true; // 标记是新增操作
                    } 
                    window.location.hash = '/vote/setting/' + args.params.id;
                } else if (scope.follow === 'save') {
                    window.location.hash = '/vote/list'; // 跳转下一步
                }
            }
            // 函数 -- 增加选项
            function addOption(_this) {
                var pNode = $(_this).parents('.j-option-unit');
                var optNode = pNode.clone();
                optNode.find('textarea').val('');
                pNode.after(optNode);

                setCheckRange(scope.optionNum);
                scope.optionNum++;

                setLimit(scope.optionNum);
            }
            // 函数 -- 删除选项
            function delOption(_this) {
                $(_this).parents('.question-option').remove();
                setCheckRange(scope.optionNum);
                scope.optionNum--;

                setLimit(scope.optionNum);
            }
            // 函数 -- 移动选项
            function changPotion(_this, pos) {
                if (pos === 'up') { // 向上移动
                    var pNode = $(_this).parents('.question-option');
                    pNode.after(pNode.prev());
                } else if (pos === 'down') { // 向下移动
                    var pNode = $(_this).parents('.question-option');
                    pNode.before(pNode.next());
                }
            }
            // 设置选项表单下拉列数
            function setCheckRange(num) {
                var optLen = $optionWrapper.children().length;
                if (optLen < num) { // 减少一个选项
                    $minChecked.children(':last').remove();
                    $maxChecked.children(':last').remove();
                } else if (optLen > num) { // 增加一个选项
                    $minChecked.append('<option value="' + optLen + '">' + optLen + '项</option>');
                    $maxChecked.append('<option value="' + optLen + '">' + optLen + '项</option>');
                }
            }
            // 设置删除选项的限制
            function setLimit(optionLenght) {
                if (optionLenght > 2) {
                    $optionWrapper.removeClass('limit');
                } else {
                    $optionWrapper.addClass('limit');
                }
            }
        },
        /* 投票功能设置 */
        setting: function (args) {
            // entry vote setting page
            // 跳转流程走向
            scope.follow = 'save';
            // jq DOM
            var $form = $('#j_vote_form'),          // 表单
                $prevBtn = $('#j_prev_btn'),        // 上一步按钮
                $publishBtn = $('#j_publish_btn'),  // 发布按钮
                $saveBtn = $('#j_save_btn'),        // 保存按钮
                $startTimeCheck = $('#assign_begin_time'),// 开始时间 radio
                $endTimeCheck = $('#assign_end_time'),   // 结束时间 radio
                $startTimeInput = $('#j_begin_time'),      // 开始时间input id
                $endTimeInput = $('#j_end_time');          // 结束时间input id
            // variable
            var formID = '#j_vote_form';            // 表单id
            // 获取设置信息
            if (Number(args.params.id)) {
                service.vote.getVoteSetting({ id: args.params.id }).done(function (res) {
                    if (res.code === 200) {
                        var info = res.data.info;
                        utils.setFormData(formID, info);
                        if (info.stime != 0) {
                            $startTimeCheck.val(info.stime).click();
                            $startTimeInput.val(info.stime)
                        }
                        if (info.etime != 0) {
                            $endTimeCheck.val(info.etime).click();
                            $endTimeInput.val(info.etime)
                        }
                        if (+info.is_publish) { // 投票已发布
                        } else {
                            $form.children(':gt(0)').removeClass('hide'); // 直显其他设置
                            $saveBtn.siblings().removeClass('hide');
                        }
                    }
                });
            }
            // 上一步
            $prevBtn.on('click', function () {
                scope.confirmPop.show()
                    .sure(function () {
                        scope.follow = 'prev';
                        $(formID).submit();
                    }).cancel(function () {
                        window.location.hash = '/vote/option/' + args.params.id;
                    });
            });
            // 发布
            $publishBtn.on('click', function () {
                scope.follow = 'publish';
                $(formID).submit();
            });
            //保存
            $saveBtn.on('click', function () {
                scope.follow = 'save';
                $(formID).submit();
            });
            // 投票功能设置表单提交
            $form.validate({
                submitHandler: function (from) {
                    if (!$.sendOK) { return; }
                    var obj = utils.getFormData($form);
                    $.extend(obj, { id: args.params.id });
                    if (scope.follow === 'publish') { // 发布
                        $.extend(obj, { etype: 0 }); // 发布字段
                        service.vote.publishVote(obj).done(function(res) {
                            if (res.code === 200) {
                                CreatePop.tip('投票已发布').afterHide(function() {
                                    if (scope.addVoteOperation) {
                                        window.BSTdao.clearFilter();
                                    }
                                    setTimeout(function() {
                                        window.location.hash = '/vote/list';
                                    }, 20);
                                });
                            }
                        });
                    } else { // 保存
                        service.vote.saveVoteSetting(obj).done(function (res) {
                            if (res.code === 200) {
                                if (scope.follow === 'prev') { // 上一步
                                    window.location.hash = '/vote/option/' + args.params.id;
                                } else { // 保存
                                    CreatePop.tip('投票已保存').afterHide(function() {
                                        if (scope.addVoteOperation) {
                                            window.BSTdao.clearFilter();
                                        }
                                        setTimeout(function() {
                                            window.location.hash = '/vote/list';
                                        }, 20);
                                    });
                                }
                            }
                        });
                    }
                }
            });
            // 时间选择插件
            var startT = {
                onClose: true,
                // minDate: $.nowDate({DD:"0"}),
                format: 'YYYY-MM-DD hh:mm:ss',
                skinCell: 'jedatered',
                okfun: function(obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    $startTimeCheck.val(obj.val()).trigger('blur');
                },
                choosefun: function (obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    $startTimeCheck.val(obj.val()).trigger('blur');
                }
            }
            var endT = {
                onClose: true,
                // minDate: $.nowDate({DD:"0"}),
                format: 'YYYY-MM-DD hh:mm:ss',
                skinCell: 'jedatered',
                okfun: function(obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    $endTimeCheck.val(obj.val()).trigger('blur');
                },
                choosefun: function (obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    $endTimeCheck.val(obj.val()).trigger('blur');
                }
            };
            $startTimeInput.jeDate(startT);
            $endTimeInput.jeDate(endT);

        },
        /* 投票列表 */
        list: function (args) {
            // entry vote list page
            // jq DOM
            var $table = $('#j_vote_table');          // 表单
            // variable
            var tableID = '#j_vote_table',          // bs table 的id
                searchFormID = '#j_form_search';    // 搜索表格的form的id
            // bs table 投票列表
            scope.voteBST = BST.extOption({
                bsTableId: tableID,
                url: service.vote.getVoteList,
                BSTSearchFromId: searchFormID,
                BSTSearchOBJPrefix: 'search',
            });
            $.publish('BSTRstoreSearch');

            // 删除投票
            var delVotePop = new CreatePop({
                type: 'confirm',
                name: 'warning',
                blast: '.j-handle-del',
                delegate: tableID,
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该投票吗？',
                sureCallBack: function () {
                    var id = scope.voteBST.clickEve.row.id; // get client id from click event
                    service.vote.deleteVote({ id: id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('删除成功！');
                            $.publish('BSTremove', [id]); // 从表格移除删除项
                        }
                    });
                }
            });
            // 发布
            var publishPop = new CreatePop({
                type: 'confirm',
                name: 'info',
                // title: '提示',
                blast: '.j-handle-publish',
                delegate: tableID,
                content: '确认要发布么？',
                sureCallBack: function () {
                    var row = scope.voteBST.clickEve.row; // get client id from click event
                    service.vote.publishVote({ id: row.id, etype: 1 }).done(function (res) {
                       if (res.code === 200) {
                           CreatePop.tip('发布成功'); // 从表格移除删除项
                           row.is_publish = '1';
                           $.publish('BSTupdate', [row]);
                       }
                    });
                }
            });
            // 预览
            var previewPop = new CreatePop({
                type: 'confirm',
                name: 'info',
                // title: '微信预览',
                blast: '.j-handle-preview',
                delegate: tableID,
                content: '投票将发送到您的微信',
                sureCallBack: function () {
                    var row = scope.voteBST.clickEve.row; // get client id from click event
                    service.vote.previewVote({ id: row.id }).done(function (res) {
                       if (res.code === 200) {
                           CreatePop.tip('投票已发送到您的微信'); 
                       }
                    });
                }
            });
        },
        /* 投票统计 */
        statistics: function (args) {
            // entry vote detail page
            // variable
            var scopeId = '#j_statistics',
                tableID = '#j_statistics_table';
            // 获取统计结果
            service.vote.getStatistics({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    utils.bindDataToDOM(res.data, 'json', scopeId);
                    utils.repeatDOM(res.data.answer, 'answer', tableID);
                }
            });
            // 设置下载统计的链接
            $('#j_download_report').attr('href', '/home/vote/downloadStatis?id=' + args.params.id);
        },
    }

});