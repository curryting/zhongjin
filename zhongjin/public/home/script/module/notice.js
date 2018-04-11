
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
        // 标记当前页面视图名称
        pageView: 'add',
    };
    return {
        /* 新增通知 */
        add: function (args) {
            // entry add notice page
            scope.pageView = 'add';
            scope.follow = 'send'; // 控制操作流程
            scope.notice_type = 1; // 通知类型（默认图文）
            // jq DOM
            var $selectObjNameInput = $('#j_selected_member'),
                $form = $('#j_add_notice_form'),            //表单 form
                $toggleNotice = $('#j_toggle_notice_type'), // 切换通知类型 btns
                $titleInput = $('#j_title_input'),          // 标题 input    
                $abstractArea = $('#j_abstract_area'),      // 摘要 textarea
                $coverIdInput = $('#j_cover_id'),           // 封面id input
                $fileNamePlace = $('#j_file_name_place'),   // 图片名称dom
                $attachIdInput = $('#j_attach_id'),         // 附件id input
                $plainAarea = $('#j_textarea_plain'),       // 纯文本 textarea
                $UETextarea = $('#j_u_editor'),             // 富文本 textarea
                $previewImg = $('#j_preview_img'),          // 选择文件下方的预览 img
                $thumb = $('#j_thumb_list'),                // 预览列
                $pushGroup = $('#j_push_official'),         // 推送到公众号
                $previewBtn = $('#j_weixin_preview');       // 预览按钮
                   
            // varable
            var formID = '#j_add_notice_form',              // 新增通知的表单的id
                UETextareaId = 'j_u_editor',                // 富文本 textarea id  
                activeIndex = 0,                            // 激活的通知的索引
                formArray = [],                             // 存储图文通知表单的 数组
                currentFormData = {};                       // 存储当前表单数据

            // 上传封面
            utils.uploadFile({
                prefix: 'notice-cover',
                url: service.notice.uploadCover,   // 上传文件url
                btn: 'j_browse_btn',        // 浏览按钮
                fileNamePlace: 'j_file_name_place', // 文件名称放置位置
                fileNameInit: $fileNamePlace.attr('title'), // 初始文件名称
                container: 'j_upload_container', // 
                previewSelector: ['#j_preview_img','#j_thumb_list li.active img'],
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
            
            // 上传文件附件
            utils.uploadFile({
                prefix: 'notice-file',
                url: service.notice.uploadFile,   // 上传文件url
                btn: 'j_upload_attach_input',        // 浏览按钮
                fileNamePlace :'j_attach_name', // 文件名称放置位置
                // fileNameInit :'j_attach_name', // 初始文件名称
                container: 'j_upload_file_container', // 
                // previewSelector: ['#j_preview_img','#j_thumb_list li.active img'],
                filter: {
                    size: '5mb',
                    ext: "txt,xml,pdf,zip,rar,tar,gz,7z,doc,ppt,xls,docx,pptx,xlsx,xlsm"
                },
                callback: function (res) { 
                    if (res.code === 200) {
                        $attachIdInput.val(res.data).valid();
                    } else {
                        CreatePop.warning(res.msg);
                    }
                },
            });
            
            // 选择发送对象
            tree.init(function(result) {
                $selectObjNameInput.val(result.names);
                delete result.names;
                scope.send_obj = result;
            });
            $('#j_show_selectOBJ').on('click', function() {
               tree.showPop();
            });

            // 定时发送pop
            new CreatePop({
                type: 'prompt',
                name: 'clocking',
                blast: '#j_clocking_btn',
                title: '定时发送',
                autoHide: false,
                sureBtn: '发送',
                afterMountCallBack: function() {
                    var that = this;
                    $('#j_clocking_input').jeDate({
                        format: 'YYYY-MM-DD hh:mm:ss',
                        // minDate: $.nowDate({DD:"0"}),
                        skinCell: 'jedatered',
                        isTime: true,
                        okfun: function(obj) {
                            $('#j_clocking_input').trigger('blur');
                        },
                        choosefun: function (obj) {
                            $('#j_clocking_input').trigger('blur');
                        }
                    });
                    this.$form.validate({
                        submitHandler: function(form) {
                            scope.setSendTime = $('#j_clocking_input').val(); // 保存设定时间
                            scope.follow = 'clocking';  // 设置表单提交为定时
                            $(formID).submit(); // 触发表单提交事件
                            that.hide();
                        }
                    });
                }
            });
            // 预览
            $previewBtn.on('click', function() {
                scope.follow = 'preview';
                $(formID).submit();
            });
            // 发送
            $form.find('button[type="submit"]').on('click', function() {
                scope.follow = 'send';
            });

            // 富文本编辑器
            var ue = utils.initUE(UE, UETextareaId, {
                initialFrameWidth: 705
            });
            
            // 富文本表单验证
            ue.addListener('contentChange', function () {
                if (scope.preventValidUE || ue.getContent()) {
                    $('.edui-editor').removeClass('has-error').parents('.editor-wrapper').siblings('span.has-error').hide()
                    scope.preventValidUE = false;
                } else {
                    $('.edui-editor').addClass('has-error').parents('.editor-wrapper').siblings('span.has-error').text('这是必填字段').show();
                }
            });
            // 富文本不能为空
            $form.on('submit', function () { !ue.getContent() && $('.edui-editor').addClass('has-error') });
            // 切换消息方式
            $toggleNotice.on('click', '.btn', function () {
                var noticeType = $(this).data('type');
                if (this.className === 'active') { return; }
                changeNoticeType(noticeType, this);
            });

            // 预览列表增，删，改，操作
            $thumb
                // 激活选项或新增
                .on('click', 'li', function (eve) {
                    if ($(eve.target).hasClass('icon')) { return }
                    activateItem(this);
                })
                // 向上移动选项
                .on('click', '.j-move-up', function () {
                    changePostion(this, 'up');
                    return false;
                })
                // 向下移动选项
                .on('click', '.j-move-down', function () {
                    changePostion(this, 'down');
                    return false;
                })
                // 删除该选项
                .on('click', '.j-del-this', function () {
                    removeItem(this);
                    return false;
                })
                // 增加通知
                .on('click', '.j-add-item', function () {
                    addItem(this);
                    return false;
                });

            // 通知表单校验/提交
            $form.validate({
                errorPlacement: function (err, ele) {
                    err.appendTo(ele.parents('.form-group'));
                },
                submitHandler: function (form) {
                    var formData = { send_type: 0, clocking: 0, notice_type: scope.notice_type };
                    // 添加发送对象/是否定时
                    if ($('[name="send_type"]:checked').val() === '1') {
                        if (scope.send_obj)  {
                            if (scope.send_obj.category[0] !== -1) {
                                formData = $.extend(formData, { send_type: 1, send_obj: scope.send_obj });
                            }
                        } else {
                            CreatePop.tip('请选择发送对象');
                            return;
                        }
                    }
                     // 确定消息类型
                    if (scope.notice_type === 1) { // 图文消息
                        // 保存当前表单
                        saveForm();
                        // 校验数组表单是否全部通过校验
                        var invalidIndex = -1;
                        var isValid = formArray.every(function (ele, index, arr) {
                            !ele.isValid && (invalidIndex = index);
                            return ele.isValid;
                        });
                        if (isValid) {
                            // 删除临时的 isValid 字段
                            formArray.forEach(function (ele, index, arr) {
                                delete ele.isValid
                            }, this);
                            // 插入表单数组
                            formData.list = formArray;
                            // 提交表单
                            submitNoticeForm(formData);
                        } else {
                            // 激活校验失败的表单
                            activateItem($thumb.children().eq(invalidIndex));
                        }
                    } else { // 纯文本或文件通知
                        // 插入当前表单数据
                        $.extend(formData, utils.getFormData($form));
                        // 提交表单
                        submitNoticeForm(formData);
                    }    
                }
            });

            // 提交表单数据
            function submitNoticeForm (obj) {
                if (!$.sendOK) { return; }
                if(scope.follow === 'preview') { // 调用预览接口
                    service.notice.previewNotice(obj).done(function(res) {
                        if (res.code === 200) {
                            CreatePop.tip('通知已送到您的微信');
                            // 彻底清空表单和预览数据,selectObj,thumbs
                            // resetForm(true);
                        }
                    });
                } else {
                    if (scope.follow === 'clocking') { // 设置定时参数
                        obj.clocking = scope.setSendTime;
                    }
                    // 提交表单
                    service.notice.addNotice(obj).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('通知已发布').afterHide(function () {
                                // 清空列表过滤条件
                                window.BSTdao.clearFilter();
                                setTimeout(function() {
                                    window.location.hash = '/notice/list';
                                }, 20);
                            });
                            // 彻底清空表单和预览数据,selectObj,thumbs
                            // resetForm(true);
                        }
                    });
                }
            }

            // 函数 -- 追加一条通知
            function addItem(_this) {
                // 保存当前表单
                saveForm();
                // 清空当前表单
                resetForm(null, true);
                // 表单数组追加表单
                formArray.push({});
                // 在底部插入预览图DOM
                var str = '<li class="active" data-title="请输入标题...">'
                    + '<div class="img"><img src=""></div>'
                    + '<div class="handle"><i class="icon icon-down j-move-down" title="下移"></i><i class="icon icon-up j-move-up" title="上移"></i><i class="icon icon-del j-del-this" title="删除"></i></div>'
                    + '</li>';
                // 添加按钮的索引号
                var index = $(_this).parent().before($(str)).index();
                // 设置激活索引
                $thumb.children().eq(activeIndex).removeClass('active');
                activeIndex = index - 1;
            }
            // 函数 -- 移除一条通知
            function removeItem(_this) {
                // 目标通知索引
                var targetIndex = $(_this).parent().parent().index();
                // 移除该 DOM
                $thumb.children().eq(targetIndex).remove();
                // 从数据中移除该表单
                formArray.splice(targetIndex, 1);
                // 判断当前表单是否激活
                if (targetIndex === activeIndex) {
                    // 清空表单
                    resetForm();
                    // 设置激活索引
                    activeIndex = $thumb.children().eq(targetIndex).prev().addClass('active').index();
                    // 设置表单数据
                    restoreForm();
                } else {
                    activeIndex = $thumb.find('li.active').index();
                }
            }
            // 函数 -- 切换激活的通知
            function activateItem(_this) {
                // 目标通知索引
                var targetIndex = $(_this).index();
                if (activeIndex === targetIndex) { return false }
                // 保存当前表单数据
                saveForm();
                // 清空当前表单数据
                resetForm();
                // 激活对应的预览图
                $thumb.children().eq(activeIndex).removeClass('active').end()
                    .eq(targetIndex).addClass('active');
                activeIndex = targetIndex; // 当前激活表单的索引
                // 设置表单数据
                restoreForm();
            }
            // 函数 -- 调整通知的顺序
            function changePostion(_this, pos) {
                var $li = $(_this).parent().parent();
                // 当前预览索引
                var index = $li.index();
                var direct = 1; // 方向标志：上加1下减1
                // 判断移动方向
                if (pos === 'down') { // 目标通知上调
                    // 调整dom顺序
                    $li.before($li.next());
                } else if (pos === 'up') { // 目标通知下调
                    var direct = -1;
                    // 调整dom顺序
                    $li.after($li.prev());
                }
                // 调整表单数据顺序
                var obj = formArray.splice(index, 1)[0];
                formArray.splice(index + direct, 0, obj);
                // 设置新的激活索引
                activeIndex = $thumb.find('li.active').index();
            }
            // 函数 -- 该变消息方式
            function changeNoticeType(type, _this) {
                switch (type) {
                    case 'text':// 文字消息
                        // 设置form为 text-type 类
                        $form.parent().attr('data-type', 'text-type');
                        // 禁用 封面/附件/摘要/富文本
                        $coverIdInput.attr('disabled', true);
                        $abstractArea.attr('disabled', true);
                        $UETextarea.attr('disabled', true);
                        $attachIdInput.attr('disabled', true);
                        // 启用 标题/纯文本
                        $titleInput.removeAttr('disabled');
                        $plainAarea.removeAttr('disabled');
                        // 标志通知类型
                        scope.notice_type = 2;
                        // 启用推送到公众号
                        $pushGroup.addClass('hide');
                        break;
                    case 'file':// 文件消息
                        // 设置form为 file-type 类
                        $form.parent().attr('data-type', 'file-type');
                        // 禁用 标题/封面/摘要/富文本/纯文本
                        $titleInput.attr('disabled', true);
                        $coverIdInput.attr('disabled', true);
                        $abstractArea.attr('disabled', true);
                        $UETextarea.attr('disabled', true);
                        $plainAarea.attr('disabled', true);
                        // 启用附件
                        $attachIdInput.removeAttr('disabled');
                        // 标志通知类型
                        scope.notice_type = 3;
                        // 启用推送到公众号
                        $pushGroup.addClass('hide');
                        break;
                    case 'default':// 默认图文消息
                        // 设置form为 default-type 类
                        $form.parent().attr('data-type', 'default-type');
                        // 启用 标题/封面/摘要/富文本
                        $titleInput.removeAttr('disabled');
                        $coverIdInput.removeAttr('disabled');
                        $abstractArea.removeAttr('disabled');
                        $UETextarea.removeAttr('disabled');
                        // 禁用 附件/纯文本
                        $attachIdInput.attr('disabled', true);
                        $plainAarea.attr('disabled', true);
                        // 标志通知类型
                        scope.notice_type = 1;
                        // 启用推送到公众号
                        $pushGroup.removeClass('hide');
                        break;
                }
                $(_this).addClass('active').siblings().removeClass('active');
            }
            // u editor 设置内容
            function UESetDoc(ue, content) {
                ue.ready(function () {
                    ue.setContent(content)
                });
            }
            // u editor 清空内容
            function UEClearDoc(ue) {
                ue.execCommand('cleardoc');
            }
            // 清空表单
            function resetForm(thorough, preventValidUE) {
                $form[0].reset();
                $coverIdInput.val('');
                $fileNamePlace.text($fileNamePlace.attr('title'));
                preventValidUE && (scope.preventValidUE = true);
                $previewImg.addClass('hide').removeAttr('src'); // 预览图
                if (thorough) { // 彻底清空
                    $selectObjNameInput.val(''); // 发送成员输入框
                    scope.send_obj = null; // 发送对象
                    scope.preventValidUE = true; // 不校验UE输入合法性
                    scope.setSendTime = 0; // 保存发定时
                    $('#j_clocking_input').val(''); // 定时
                    $thumb.children().eq(0).attr('data-title', '').find('img').removeAttr('src').end()
                    .siblings().not(':last').remove();
                }
                UEClearDoc(ue);
            }
            // 保存表单
            function saveForm() {
                var obj = $.extend({}, { isValid: $form.valid() }, utils.getFormData(formID));
                formArray.length ? formArray[activeIndex] = obj : formArray.push(obj);
            }
            // 恢复表单
            function restoreForm() {
                var obj = formArray[activeIndex];
                utils.setFormData(formID, obj);
                UESetDoc(ue, obj.content);
                $form.valid();
                var imgSrc = $thumb.children().eq(activeIndex).find('img').data('src');
                if (imgSrc) {
                    $previewImg.attr('src', imgSrc).removeClass('hide');
                } else {
                    $previewImg.addClass('hide');
                }
            }


        },
        /* 通知列表 */
        list: function (args) {
            // entry notice list page
            scope.pageView = 'list';
            // bs table 通知列表
            scope.noticeBST = BST.extOption({
                bsTableId: '#j_notice_table',
                url: service.notice.getNoticeList,
                BSTSearchFromId: '#j_search_form',
                BSTSearchOBJPrefix: 'search',
            });
            // 页面刷新恢复上一次搜索
            $.publish('BSTRstoreSearch');

            // 删除通知
            var delNoticePop = new CreatePop({
                type: 'confirm',
                name: 'warning',
                blast: '.j-handle-del',
                delegate: '#j_notice_table',
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该通知吗？',
                sureCallBack: function () {
                    var id = scope.noticeBST.clickEve.row.id; // get client id from click event
                    service.notice.deleteNotice({ id: id }).done(function (res) {
                        if (res.code === 200) {
                            CreatePop.tip('删除成功！');
                            $.publish('BSTremove', [id]); // 从表格移除删除项
                        }
                    });
                }
            });
            // 撤销通知
            var revokeNoticePop = new CreatePop({
                type: 'confirm',
                name: 'warning',
                // title: '警告',
                blast: '.j-handle-revoke',
                delegate: '#j_notice_table',
                content: '您确定要撤销该通知吗？',
                sureCallBack: function () {
                    var row = scope.noticeBST.clickEve.row; // get client id from click event
                    service.notice.revokeNotice({ id: row.id }).done(function (res) {
                        if (res.code === 200) {
                            row.status = 3;
                            CreatePop.tip('通知已撤销');
                            $.publish('BSTupdate', [row]); // 从表格移更新项
                        }
                    });
                }
            });
        },
        /* 通知详情 */
        detail: function (args) {
            // entry notice detail page
            scope.pageView = 'detail';
            // jq DOM
            var $getCommentBtn = $('#j_more_comments'), // 请求评论 button 
                $loadingTip = $('#j_loading_tip'), // 加载提示
                $scrollContent = $('#j_scroll_content'), // 滚动区域
                $commentsList = $('#j_comments_list');
            // varable
            var commentsListID = '#j_comments_list',        // 评论列表 ul id
                noticeScopeID = '#j_notice_detail',         // 绑定通知详情上下文 id
                getReadBtn = '#j_had_read',     // 已读btn
                getNotReadBtn = '#j_not_read';  // 未读btn
                
            // 获取通知详情,绑定数据
            service.notice.getNoticeDetail({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    utils.bindDataToDOM(res.data.detail, 'notice', noticeScopeID);
                }
            });
            
            // 获取通知评论
            utils.getComments({
                url: service.notice.getComments,
                param: { id: args.params.id, offset: 0, limit: 15 },
                $tip: $loadingTip,
                $scrollContent: $scrollContent,
                success: function(res) { utils.repeatDOM(res, 'comment', commentsListID, true); },
                error: function(err) { CreatePop.warning(err) },
            });
            // 获取评论回复
            utils.getReply({
                url: service.notice.getCommentReply,
                method: 'post',
                commentsList: $commentsList,
                delegate: '.j-get-reply-btn',
                limit: 5
            });
            // 显示已读列表
            utils.getHadReadMember({
                url: service.notice.getHadReadList,
                param: { id: args.params.id },
                popBlast: getReadBtn
            });
            // 显示未读列表
            utils.getNotReadMember({
                url: service.notice.getNotReadList,
                remindUrl: service.notice.remindRead,
                param: { id: args.params.id },
                popBlast: getNotReadBtn
            });
        },
    }
});