
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
    'xiumiV5',
], function ($, service, utils, CreatePop, DAO, BST, tree) {
    'use strict';
    /*********** 模块公用函数 ***********/
    // 数据中心 map data
    var scope = {
        // 页面是否刷新
        refresh: true,
        // 在编辑的调研的id
        surveyId: 1,
    };

    // DAO
    var dao = new DAO('survey');
    return {
        /* 新增调研 */
        add: function (args) {
            // entry add survey page
            scope.follow = 'next';  // 操作流程(新增或更新)
            // jq DOM
            var $thumbImg = $('.j-thumb-img'),              // 右侧预览图
                $previewImg = $('#j_preview_img'),          // 选择文件下方的预览 img

                $editorWrapper = $('#j_editor_wrapper'),    // 富文本wrapper 
                $form = $('#j_survey_form'),                // 新增新闻的表单 form
                $coverIdInput = $('#j_cover_id'),           // 隐藏的封面id的输入框  
                $selectObjNameInput = $('#j_selected_member'),
                $startTime = $('#assign_begin_time'),       // 开始时间 radio
                $endTime = $('#assign_end_time'),           // 结束时间 radio
                // $nextBtn = $('#j_next_btn'),                // 提交表单按钮
                $saveBtn = $('#j_save_btn'),                // 编辑时显示保存按钮
                $previewBtn = $('#j_preview_btn'),          // 微信预览按钮
                $previewTime = $('.j-preview-time'),        // 右侧预览时间戳
                $previewDes = $('#j_preview_des');          // 右侧预览描述

            // varable
            var formID = '#j_survey_form',                  // 新增调研的表单的id
                UETextareaId = 'j_u_editor',                // 富文本 textarea id  
                startTimeId = '#j_begin_time',              // 开始时间input id
                endTimeId = '#j_end_time',                  // 结束时间input id
                previewContentId = '#j_preview_content';    // 绑定数据上下文


            // 选择发送对象
            tree.init(function(result) {
                $selectObjNameInput.val(result.names);
                delete result.names;
                scope.send_obj = result;
            });
            // scope.send_obj && tree.setData(scope.send_obj);
            $('#j_show_selectOBJ').on('click', function() {
                tree.showPop();
            });

            // 富文本编辑器
            var ue = utils.initUE(UE, UETextareaId, {
                initialFrameWidth: 705
            }, true);
     
            // 获取调研内容
            if (Number(args.params.id)) {
                $saveBtn.removeClass('hide');
                service.survey.getSurveyContent({ id: args.params.id }).done(function (res) {
                    if (res.code === 200) {
                        var detail = res.data.detail;
                        // 设置表单数据
                        detail.start_time != 0 && $startTime.val(detail.start_time).next().next().val(detail.start_time);
                        detail.end_time != 0 && $endTime.val(detail.end_time).next().next().val(detail.end_time);
                        utils.setFormData(formID, detail);
                        utils.bindDataToDOM(detail, 'preview', previewContentId);
                        detail.cover && $previewImg.attr('src', detail.cover).removeClass('hide');
                        // 设置选择发送对象数据
                        if (detail.send_type === 1){
                            scope.send_obj = $.extend({ category: [], member: [] }, detail.send_obj);
                            tree.setData(scope.send_obj);
                            $selectObjNameInput.val(detail.names);
                        } 
                        // 设置富文本数据
                        ue.ready(function () {
                            detail.description && ue.setContent(detail.description)
                        });
                    }
                });
            }

            // 上传封面
            utils.uploadFile({
                prefix: 'survey-cover',
                url: service.survey.uploadCover,   // 上传文件url
                btn: 'j_browse_btn',        // 浏览按钮
                fileNamePlace: 'j_file_name_place', // 文件名称放置位置
                fileNameInit: '(推荐尺寸：900*500,图片大小不超过64K)', // 初始文件名称
                container: 'j_upload_container', // 
                previewSelector: ['#j_preview_img','#j_preview_content .j-thumb-img'],
                filter: {
                    size: '2mb',
                    ext: "png,jpg,jpeg,bmp"
                },
                callback: function (res) { 
                    if (res.code === 200) {
                        $coverIdInput.val(res.data).valid();
                        scope.cover =  $previewImg.attr('src');
                    } else {
                        CreatePop.warning(res.msg);
                    }
                },
            });
            $saveBtn.on('click', function() {
                scope.follow = 'save';
                $(formID).submit();
            });
            // 表单提交
            $form.validate({
                errorPlacement: function (err, ele) {
                    err.appendTo(ele.parents('.form-group'));
                },
                submitHandler: function (form) {
                    // 保存当前表单
                    var obj = $.extend(utils.getFormData(formID), { send_type: 0 });
                    // 发送对象
                    if ($('[name="send_type"]:checked').val() === '1') {
                        if (scope.send_obj)  {
                            if (scope.send_obj.category[0] !== -1) {
                                $.extend(obj, { send_type: 1, send_obj: scope.send_obj });
                            }
                        } else {
                            CreatePop.tip('请选择发送对象');
                            return;
                        }
                    }
                    // 结束语
                    obj.epilogue || (obj.epilogue = '您的答案已提交，感谢您的参与！');
                    if (!$.sendOK) { return; }
                    if (Number(args.params.id)) { // 当前为编辑
                        obj.id = args.params.id;
                        service.survey.updateSurveyContent(obj).done(function (res) {
                            if (res.code === 200) {
                                if (scope.follow === 'save') {
                                    window.location.hash = '/survey/list'; // 跳转到列表
                                } else {
                                    window.location.hash = '/survey/option/' + args.params.id; // 跳转页面
                                }
                            }
                            
                        });
                    } else { // 当前为新增
                        service.survey.addSurveyContent(obj).done(function (res) {
                            res.code === 200 && (window.location.hash = '#/survey/option/' + res.data.id); // 跳转下一步
                        });
                    }
                }
            });
   
            // 同步输入到预览/表单验证
            ue.addListener('contentChange', function () {
                scope.ueContent = ue.getContent();
                $previewDes.html(scope.ueContent);
                scope.ueContent ? $('.edui-editor').removeClass('has-error').parents('.editor-wrapper').next().hide() : $('.edui-editor').addClass('has-error').parents('.editor-wrapper').next().text('这是必填字段').show();
            });

            // 富文本不能为空
            $form.on('submit', function () { !ue.getContent() && $('.edui-editor').addClass('has-error') });

            // 时间选择插件
            var startT = {
                onClose: true,
                // minDate: $.nowDate({DD:"0"}),
                format: 'YYYY-MM-DD hh:mm:ss',
                skinCell: 'jedatered',
                okfun: function(obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    $startTime.val(obj.val());
                },
                choosefun: function (obj) {
                    endT.minDate = obj.val(); //开始日选好后，重置结束日的最小日期
                    $startTime.val(obj.val());
                }
            };
            var endT = {
                onClose: true,
                format: 'YYYY-MM-DD hh:mm:ss',
                // minDate: $.nowDate({DD:"0"}),
                skinCell: 'jedatered',
                okfun: function(obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    $endTime.val(obj.val());
                },
                choosefun: function (obj) {
                    startT.maxDate = obj.val(); //将结束日的初始值设定为开始日的最大日期
                    $endTime.val(obj.val());
                }
            };
            $(startTimeId).jeDate(startT);
            $(endTimeId).jeDate(endT);

            // 预览时间戳
            $previewTime.text(new Date().toLocaleDateString().replace(/\//g, '-'));

        },
        /* 调研选项 */
        option: function (args) {
            // entry survey option page
            scope.follow === 'save';        // 操作流程
            scope.hasQuestionDetail = false; // 标记当前表单是否有原始数据
            scope.formArray = [];           // 表单数组
            scope.invalidIndex = -1;        // 校验不通过的表单索引
            // jq DOM
            var $prevBtn = $('#j_prev_btn'),    // 上一步按钮
                $publishBtn = $('#j_publish_btn'),// 发布按钮
                $saveBtn = $('#j_save_btn'),    // 保存按钮    
                $form = $('#j_survey_form'),    // 表单   
                $formWrapper = $('#j_form_wrapper'), // 表单wrapper
                $addQuesBtn = $('#j_add_question'), // 增加问题按钮 
                $previewOpt = $('#j_preview_opt'),  // 选项预览区
                $minChecked = $('#j_min_ck'),           // 最少选择多少项 select
                $maxChecked = $('#j_max_ck');           // 最多选择多少项 select   

            // varable
            var formID = '#j_survey_form',      // 表格id
                preivewContentId = '#j_preview_content',// 绑定数据上下文      
                removeBtn = '.j-remove-form'; // 删除表单按钮
            // 获取调研选项
            service.survey.getSurveyOption({ id: args.params.id }).done(function (res) {
                if (res.code === 200 && res.data.detail.length) {
                    // 设置表单数据，预览数据
                    var quesArray = res.data.detail; // 选项列表
                    // 保存表单源数据
                    scope.formArray = quesArray || [];
                    // 添加临时属性valid
                    scope.formArray.forEach(function(ele, index) {
                        ele.valid = true;
                    });
                    // 标记 有detail，则提交数据时需要带上question_id字段
                    scope.hasQuestionDetail = true; 
                    for (var i = 0; i < quesArray.length; i++) {
                        // 克隆form
                        if (i > 0) {
                            cloneForm();
                            clonePreview();
                        }
                        // 当前表单和对应的预览
                        var $formNow = $formWrapper.children().eq(i), 
                            $preOptNow = $previewOpt.children().eq(i);

                        // 设置问题类型/是否必答/问题标题
                        $formNow.find('input[name="title"]').val(quesArray[i].title).trigger('input');
                        $formNow.find('select[name="type"]').val(quesArray[i].type).trigger('change');
                        $formNow.find('input[name="question_id"]').val(quesArray[i].question_id);
                        quesArray[i].required == 1 && $formNow.find('.j-required-label').click();

                        // 设置问题选项
                        if (quesArray[i].type == 1 || quesArray[i].type == 2) { // 选择题

                            var option = quesArray[i].option;                               
                            if (option) { // 问题选项
                                for (var j = 0; j < option.length; j++) {
                                    if (j < 2) {
                                        $formNow.find('textarea').eq(j).val(option[j]).trigger('input');
                                    } else {
                                        $formNow.find('.j-option-unit:last')
                                            .find('span.add').trigger('click');
                                        $formNow.find('.j-option-unit:last')
                                            .find('textarea:last').val(option[j]).trigger('input');
                                    }
                                }
                            }
                            if (quesArray[i].type == 2) { // 多选限制
                                $formNow.find('.limit').find('select[name="least"]').val(quesArray[i].least).siblings('select').val(quesArray[i].most);
                            }
                        } else {
                        }
                    }
                }
            });
     
            // 添加问题
            $addQuesBtn.on('click', function () {
                // 保存表单
                saveCurrentForm();
                // 插入新表单
                cloneForm();

                setFormLimit(2);

                $('.j-q-form.active').validate();
                // push数据
                scope.formArray.push({});
                // clone预览
                clonePreview();
            });
            // 激活/移除表单
            $formWrapper
                // 移除
                .on('click', removeBtn, function (e) {
                    // 移除表单数据
                    removeForm(this);
                    
                    setFormLimit($formWrapper.children('form').length);
                    e.stopPropagation();
                    return false;
                })
                // 激活
                .on('click', 'form', function () {
                    if ($(this).hasClass('active')) {
                        return;
                    }
                    // 保存表单数据
                    saveCurrentForm();
                    // 激活表单
                    $(this).addClass('active').siblings().removeClass('active');
                });


            // 上一步，提示保存
            $prevBtn.on('click', function () {
                CreatePop.confirm('info', '是否保存当前表单?')
                    .sure(function () {         // 保存表单，跳转到上一步
                        scope.follow = 'prev';  // 标记跳转为上一步
                        submitForms();
                    }).cancel(function () {     // 直接跳转到上一步
                        window.location.hash = '/survey/add/' + args.params.id;
                    });
            });
            // 发布
            $publishBtn.on('click', function () {
                scope.follow = 'publish';
                submitForms();
            });
            // 保存
            $saveBtn.on('click', function () {
                scope.follow = 'save';
                submitForms();
            });
            // 右侧预览
            $previewOpt.on('click', 'ol>li', function () {
                if ($(this).parent().prev().attr('data-type') == 1) {
                    $(this).addClass('active').siblings().removeClass('active');
                } else {
                    $(this).toggleClass('active');
                }
            });
            // 函数 -- 克隆表单
            function cloneForm() {
                $formWrapper.children(':first').clone().removeAttr('id').addClass('limit').find('.question-option:gt(1)').remove().end()
                    .insertBefore($addQuesBtn).addClass('active').find('textarea').attr('class', 'invalid').end()
                    .find('select[name="type"]').trigger('change').end()
                    .find('input[name="question_id"]').val('').end()
                    .find('.form-group.limit').find('select').find('option:gt(1)').remove().end().end().end()
                    .siblings().removeClass('active').end()[0].reset();
            }
            // 移除 -- 表单
            function removeForm(_this) {
                var $targetForm = $(_this).parents('.j-q-form'),
                    targetIndex = $targetForm.index();
                scope.formArray.splice(targetIndex, 1);
                // 移除表单dom移除表单dom
                if ($targetForm.hasClass('active')) {
                    $(_this).parents('.j-q-form').prev().addClass('active').end().remove();
                } else {
                    $(_this).parents('.j-q-form').remove();
                }
                // 移除预览
                $previewOpt.children('li').eq(targetIndex).remove();
            }
            // 函数 -- 克隆预览
            function clonePreview() {
                $previewOpt.children(':first').clone().appendTo($previewOpt).attr('data-rq', "")
                    .find('.txt').text('请输入标题...').attr('data-type', 1).end()
                    .find('.options').children('li').text('选项描述').filter(':gt(1)').remove();
            }
            // 函数 -- 保存当前表单数据
            function saveCurrentForm() {
                var $activeForm = $formWrapper.find('.j-q-form.active'),
                    activeIndex = $activeForm.index();
                // valid form
                $activeForm.valid();
                // get form data
                scope.formArray[activeIndex] = getFormData('.j-q-form.active');
            }
            // 函数 -- 提交表单数据
            function submitForms() {
                // 保存激活的表单
                saveCurrentForm();
                // invalid form's index
                scope.invalidIndex = -1;
                // 校验表单所有表单
                var isValid = scope.formArray.every(function (ele, index, arr) {
                    ele.valid ? null : scope.invalidIndex = index;
                    return ele.valid;
                }, this);
                // 校验全部通过
                if (isValid) {
                    // 删除临时字段 valid
                    scope.formArray.forEach(function(ele, index, arr) {
                        delete ele.valid;
                    });

                    // judge save api
                    if (scope.hasQuestionDetail) { // 有原始数据，调用更新接口
                        var saveAPI = service.survey.updateSurveyOption;
                    } else {
                        var saveAPI = service.survey.addSurveyOption;
                    }
                    // form data
                    var formData = { id: args.params.id, questions: scope.formArray };
                    if (!$.sendOK) { return; }
                    if (scope.follow === 'prev') { // 上一步
                        saveAPI(formData).done(function(res) {
                            if (res.code === 200) {
                                window.location.hash = '/survey/add/' + args.params.id;
                            }
                        });
                    } else if (scope.follow === 'publish') { // 发布
                       formData.type = 0;
                       service.survey.publishSurvey(formData).done(function(res) {
                            if (res.code === 200) {
                                CreatePop.tip('调研已发布').afterHide(function() {
                                    if (!scope.hasQuestionDetail) { // add survey
                                        // 清空列表过滤条件
                                        window.BSTdao.clearFilter();
                                    }
                                    setTimeout(function() {
                                        window.location.hash = '/survey/list';
                                    }, 20);
                                });
                            }
                       });
                    } else if (scope.follow === 'save') { // 保存 
                        saveAPI(formData).done(function(res) {
                            if (res.code === 200) {
                                CreatePop.tip('调研已保存').afterHide(function() {
                                    if (!scope.hasQuestionDetail) { // add survey
                                        // 清空列表过滤条件
                                        window.BSTdao.clearFilter();
                                    }
                                    setTimeout(function() {
                                        window.location.hash = '/survey/list';
                                    }, 20);
                                });
                            }
                        });
                    }
                } else {
                    // 激活校验不通过的表单
                    var invalidForm = $formWrapper.find('.j-q-form').eq(scope.invalidIndex);
                    invalidForm.addClass('active')
                        .find('.invalid').addClass('has-error').end()
                        .siblings().removeClass('active').end().valid();
                }
            }
            // 函数 -- 表单取值并校验
            function getFormData(selector) {
                var obj = {},
                    valid = $(selector).serializeArray().every(function (ele, index, arr) {
                        if (obj[ele.name] instanceof Array) { // 键name已存在，为数组
                            // push value
                            obj[ele.name].push(ele.value);
                        } else if (typeof obj[ele.name] === 'string') { // 键name已存在，为对象
                            // parse obj to array
                            var tmpArr = new Array(obj[ele.name]);
                            tmpArr.push(ele.value);
                            obj[ele.name] = tmpArr;
                        } else {
                            obj[ele.name] = ele.value;
                        }
                        if (ele.name === 'question_id') {
                           return true;
                        } else {
                            return !!ele.value;
                        }
                    }, this);
                // 标记校验结果
                obj.valid = valid;
                return obj;
            }
            // 函数 -- 设置删除问题的限制
            function setFormLimit(formLength) {
                if (formLength > 1) {
                    $formWrapper.removeClass('limit');
                } else {
                    $formWrapper.addClass('limit');
                }
            }
        },
        /* 调研列表 */
        list: function (args) {
            // entry survey list page
            // jq DOM
            var $table = $('#j_survey_table');      // 调研表格    
            // varable
            var tableID = '#j_survey_table',        // bs tabe 的id
                searchFormID = '#j_form_search';    // 搜索表格的form的id   
            // bs table 调研列表
            scope.surveyBST = BST.extOption({
                bsTableId: tableID,
                url: service.survey.getSurveyList,
                BSTSearchFromId: searchFormID,
                BSTSearchOBJPrefix: 'search',
            });
            // 恢复搜索参数
            $.publish('BSTRstoreSearch');

            // 删除调研
            var delPop = new CreatePop({
                type: 'confirm',
                name: 'warning',
                // title: '警告',
                blast: '.j-handle-del',
                delegate: tableID,
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该调研吗？',
                sureCallBack: function () {
                    var id = scope.surveyBST.clickEve.row.id; // get client id from click event
                    service.survey.deleteSurvey({ id: id }).done(function (res) {
                        res.code === 200 && $.publish('BSTremove', [id]); // 从表格移除删除项
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
                    var row = scope.surveyBST.clickEve.row; // get client id from click event
                    service.survey.publishSurvey({ type: 1,  id: row.id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('发布成功'); // 从表格移除删除项
                            row.is_publish = 1;
                            $.publish('BSTupdate', [row]);
                        } 
                    });
                }
            });
            // 预览
             var previewPop = new CreatePop({
                type: 'confirm',
                name: 'info',
                // title: '提示',
                blast: '.j-handle-preview',
                delegate: tableID,
                content: '此调研将发送到您的微信，请注意查收',
                sureCallBack: function () {
                    var id = scope.surveyBST.clickEve.row.id; // get client id from click event
                    service.survey.previewSurvey({ id: id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('已发送到您的微信');
                        }
                    });
                }
            });
        },
        /* 调研统计 */
        statistics: function (args) {
            // entry survey statistics page
            // jq dom
            var $surveyTitle = $('#j-statistic-title'),
                $resultTable = $('.j-statistic-result'), // 统计列表
                $downloadLink = $('#j_download_report'); // 下载链接
               
            // 获取统计数据
            service.survey.getStatistics({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    $surveyTitle.text(res.data.title); // set survey title
                    if (res.data.info.length) { // set survey table
                        concatHTML(res.data.info);
                        // 设置下载统计链接
                        $downloadLink.attr('href', '/home/survey/downloadStatis?id=' + args.params.id).removeClass('hide');
                    } else { // tips
                        $resultTable.addClass('hide').next().removeClass('hide');
                    }
                }
            });

            // 选择题
            function concatHTML(info) {
                var types = ['【单选】', '【多选】', '【简答】', '【简答】'],
                    pNode = $resultTable,
                    cloneNode = null;
                    // 遍历问题
                for (var i = 0; i < info.length; i++) {
                    var title = '',
                        trs ='',
                        ths = '';
                    // title string
                    title = '<span class="title">第' + (i + 1) + '题：' + info[i].title + '</span>\
                            <span class="check-type">' + types[Number(info[i].type) - 1] + '</span>';
                    // table's th,tr string
                    if (info[i].type < 3) { // 单选、多选列表
                        ths = '<tr><th>选项</th><th>票数</th><th>占比</th></tr>';
                        for (var j = 0; j < info[i].answer.length; j++) {
                            trs += '<tr>\
                                        <td>' + info[i].answer[j].title + '</td>\
                                        <td>' + info[i].answer[j].count + '</td>\
                                        <td>' + info[i].answer[j].rate + '</td>\
                                    </tr>';
                        }
                    } else { // 简答列表
                        ths = '<tr><th>参与人数</th></tr>';
                        trs = '<tr><td>' + info[i].count + '</td></tr>';
                    }
                    // table now 
                    cloneNode = i > 0 ? pNode.clone() : pNode;
                    // set title
                    cloneNode.find('.j-question-title').html(title);
                    // set thead
                    cloneNode.find('thead').html(ths);
                    // set tbody
                    cloneNode.find('tbody').html(trs);
                    // apend dom 
                    i > 0 && pNode.after(cloneNode);
                    // set new pNode
                    pNode = cloneNode;
                }
            }
        },
        /* 答案列表 */
        paperList: function (args) {
            // entry survey paper list page
            // jq DOM
            var $table = $('#j_paper_table'),       // 答卷表格    
                $hideForm = $('#j_form_search'),    // 隐藏表单
                $downloadLink = $('#j_download_report'); // 下载链接 
            // varable
            var tableID = '#j_paper_table',         // bs tabe 的id
                paperList = '#j_paper_list',        // 绑定数据上下文
                formSearchId = '#j_form_search';

            // 设置隐藏表单id
            $hideForm.find('input').val(args.params.id);

            // bs table 答卷列表
            scope.paperBST = BST.extOption({
                bsTableId: tableID,
                queryObj: { id: args.params.id },
                url: service.survey.getPaperList,
                successCallback: function(res) {
                    utils.bindDataToDOM(res, 'paper', paperList)
                }   
            });
            // 设置下载报告链接
            $downloadLink.attr('href', '/home/survey/downloadReport?id=' + args.params.id);
        },
        /* 答案详情 */
        paperDetail: function (args) {
            // entry survey paper detail page
            // jq dom
            var $btnWrapper = $('#j_btn_href'), // 按钮父级
                $goBackBtn = $('#j_go_back'),   // 返回键
                $downloadLink = $('#j_download_report'); // 下载链接 
            // variable
            var paperDetailId = '#j_paper_detail',      // 绑定数据上下文
                tableID = '#j_answer_table',            // 表格id
                queryObj = { 
                    survey_id: args.params.sid, // survey id
                    id: args.params.pid,    // paper id
                    memid: args.params.mid, // member id
                },
                thumb = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'O'];
            // 获取调研详情
            service.survey.getPaperDetail(queryObj).done(function (res) {
                if (res.code === 200) {
                    utils.bindDataToDOM(res.data, 'paper', paperDetailId);
                    repeatAnswer(res.data.detail, tableID);
                    // 上一份，下一份按钮
                    if (res.data.preInfo instanceof Array && res.data.preInfo.length === 0) {
                        // 空数组,无上一份
                        $btnWrapper.children().eq(0).find('button').removeClass().attr('disabled', true);
                    } else {
                        $btnWrapper.children().eq(0).attr('href', '#/survey/paper/detail/' + args.params.sid + '/' + res.data.preInfo.id + '/' + res.data.preInfo.memid);
                    }   
                    // next btn
                    if (res.data.nextInfo instanceof Array && res.data.nextInfo.length === 0) {
                        // 空数组,无上一份
                        $btnWrapper.children().eq(1).find('button').removeClass().attr('disabled', true);
                    } else {
                        $btnWrapper.children().eq(1).attr('href', '#/survey/paper/detail/' + args.params.sid + '/' + res.data.nextInfo.id + '/' + res.data.nextInfo.memid);
                    }  
                }
            });
            // 返回paper list
            $goBackBtn.on('click', function() {
                window.location.hash = '/survey/paper/list/' + args.params.sid;
            });
            // 设置下载答卷链接
            $downloadLink.attr('href', '/home/survey/downloadAnswer?id=' + args.params.pid + '&survey_id=' + args.params.sid + '&memid=' + args.params.mid);
            // 循环生成表格 tr 
            function repeatAnswer(arr, pNodeId) {
                var str = '';
                arr.forEach(function (ele, index, arr) {
                    str += identify(ele, index);
                }, this);
                $(pNodeId).html(str);
            }
            // 判断问题类型
            function identify(ele, index) {
                var str = '';
                switch (String(ele.type)) {
                    case '1': // 单选
                        var answerIndex = Number(ele.answer) - 1,
                            answer = ele.option[answerIndex];
                        str = '<tr>'
                            + '<td><span>第' + (index + 1) + '题：</span><span>' + ele.title + '</span><span class="check-type">【单选】</span></td>'
                            + '<td><span>选项' + thumb[answerIndex] + '：</span><span>' + answer + '</span></td>'
                            + '</tr>';
                        break;
                    case '2': // 多选
                        var tmp = '',
                            answerIndex = 0,
                            answer = '';
                        for (var i = 0; i < ele.answer.length; i++) {
                            answerIndex = Number(ele.answer[i]) - 1;
                            answer = ele.option[answerIndex];
                            tmp += '<li><span>选项' + thumb[answerIndex] + '：</span><span>' + answer + '</span></li>';
                        }
                        str = '<tr>'
                            + '<td><span>第' + (index + 1) + '题：</span><span>' + ele.title + '</span><span class="check-type">【多选】</span></td>'
                            + '<td><ul>' + tmp + '</ul></td>'
                            + '</tr>';
                        break;
                    default : // 简答
                        str = '<tr>'
                            + '<td><span>第' + (index + 1) + '题：</span><span>' + ele.title + '</span><span class="check-type">【简答】</span></td>'
                            + '<td><span>' + ele.answer + '</span></td>'
                            + '</tr>';
                        break;

                }
                return str;
            }
        },
    }
});