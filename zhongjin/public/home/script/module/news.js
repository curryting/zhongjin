
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
        // 标记当前页面视图名称
        pageView: 'add',

        // 新建新闻表单
        addNewsForm: [],

    };


    /* 路由执行函数 */
    return {
        /* 新建新闻 */
        add: function (args) {
            // entry add news page
            scope.pageView = 'add';
            scope.follow = 'send';  // 默认操作流程
            // jq DOM
            var $selectObjNameInput = $('#j_selected_member'),
                $previewImg = $('#j_preview_img'),      // 选择文件下方的预览 img
                $previewBtn = $('#j_weixin_preview'),   // 微信预览按钮
                $form = $('#j_add_news_form'),          // 新增新闻的表单 form
                $thumb = $('#j_thumb_list'),            // 预览列表 ul li
                $fileNamePlace = $('#j_file_name_place'), // 图片名称dom
                $coverIdInput = $('#j_cover_id');       // 封面id
            //  varable
            var formID = '#j_add_news_form',            // 新增新闻的表单的id
                UETextareaId = 'j_u_editor',            // 富文本 textarea id  
                activeIndex = 0,                        // 激活的新闻的索引
                formArray = [];                         // 存储新闻表单的数组
                
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
                // 增加新闻
                .on('click', '.j-add-item', function () {
                    addItem(this);
                    return false;
                });

            // 上传图片
            utils.uploadFile({
                prefix: 'news-cover',
                url: service.news.uploadCover,   // 上传文件url
                btn: 'j_browse_btn',        // 浏览按钮
                fileNamePlace: 'j_file_name_place', // 文件名称放置位置
                fileNameInit:  $fileNamePlace.attr('title'), // 初始文件名称
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
            // 新闻表单提交
            $form.validate({
                errorPlacement: function (err, ele) {
                    err.appendTo(ele.parents('.form-group'));
                },
                submitHandler: function (form) {
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
                        // 添加发送对象
                        var formData = { list: formArray, send_type: 0 };
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
                        // 提交表单
                        if (!$.sendOK) { return; }
                        if (scope.follow === 'send') { // 发布
                            service.news.addNews(formData).done(function (res) {
                                if (res.code === 200) {
                                    CreatePop.tip('新闻已发布').afterHide(function() {
                                        // 清空列表过滤条件
                                        window.BSTdao.clearFilter();
                                        // redirect
                                        setTimeout(function() {
                                            window.location.hash = '/news/list';
                                        }, 20);
                                    });
                                }
                            });
                        } else if (scope.follow === 'preview') { // 预览
                            service.news.previewNews(formData).done(function (res) {
                                if (res.code === 200) {
                                    CreatePop.tip('已将内容发送至您的微信');
                                }
                            });
                        }
                    } else {
                        // 激活校验失败的表单
                        activateItem($thumb.children().eq(invalidIndex));
                    }
                }
            });
            // 提交表单执行预览
            $previewBtn.on('click', function () {
                scope.follow = 'preview';
                $(formID).submit();
            });
            // 提交表单执行发送
            $form.find('button[type="submit"]').on('click', function() {
                scope.follow = 'send';
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
           
            // 富文本编辑器
            var ue = utils.initUE(UE, UETextareaId, {
                initialFrameWidth: 705
            });
            
            // 表单验证
            ue.addListener('contentChange', function () {
                if (scope.preventValidUE || ue.getContent()) {
                    $('.edui-editor').removeClass('has-error').parents('.editor-wrapper').next().hide();
                    scope.preventValidUE = false;
                } else {
                    $('.edui-editor').addClass('has-error').parents('.editor-wrapper').next().text('这是必填字段').show();
                }
            });
            // 富文本不能为空
            $form.on('submit', function () { !ue.getContent() && $('.edui-editor').addClass('has-error') });

            // 函数 -- 追加一条新闻
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
            // 函数 -- 移除一条新闻
            function removeItem(_this) {
                // 目标新闻索引
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
            // 函数 -- 切换激活的新闻
            function activateItem(_this) {
                // 目标新闻索引
                var targetIndex = $(_this).index();
                if (activeIndex === targetIndex) { return false }
                // 保存当前表单数据
                saveForm();
                // 清空表单
                resetForm();
                // 激活对应的预览图
                $thumb.children().eq(activeIndex).removeClass('active').end()
                    .eq(targetIndex).addClass('active');
                activeIndex = targetIndex; // 当前激活表单的索引
                // 设置表单数据
                restoreForm();
            }
            // 函数 -- 调整新闻的顺序
            function changePostion(_this, pos) {
                var $li = $(_this).parent().parent();
                // 当前预览索引
                var index = $li.index();
                var direct = 1; // 方向标志：上加1下减1
                // 判断移动方向
                if (pos === 'down') { // 目标新闻上调
                    // 调整dom顺序
                    $li.before($li.next());
                } else if (pos === 'up') { // 目标新闻下调
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
                    $selectObjNameInput.val('');
                    scope.send_obj = null;
                    scope.preventValidUE = true;
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
        /* 新闻列表 */
        list: function (args) {
            // entry news list page
            scope.pageView = 'list';
            // varable
            var tableID = '#j_news_table',          // bs table 的id
                searchFormID = '#j_form_search',    // 搜索表格的form的id   
                getReadBtn = '#j_not_read',         // 已读btn
                getNotReadBtn = '#j_news_table';    // 未读btn
            // bs table 新闻列表
            scope.newsBST = BST.extOption({
                bsTableId: tableID,
                url: service.news.getNewsList,
                BSTSearchFromId: searchFormID,
                BSTSearchOBJPrefix: 'search',
            });
            // 恢复搜索参数
            $.publish('BSTRstoreSearch');

            // 日期插件
            $('#create_time').jeDate({
                format: 'YYYY-MM-DD',
                skinCell: "jedatered",
                isTime: false,
            });

            // 删除一条新闻
            var delNewsPop = new CreatePop({
                type: 'confirm',
                name: 'warning',
                blast: '.j-handle-del',
                delegate: '#j_news_table',
                content: '删除后无法恢复，谨慎操作！\r您确定要删除该新闻吗？',
                sureCallBack: function () {
                    var id = scope.newsBST.clickEve.row.id; // get client id from click event
                    service.news.deleteNews({ id: id }).done(function (res) {
                       if (res.code === 200) {
                            CreatePop.tip('删除成功！');
                            // 从表格移除删除项
                            $.publish('BSTremove', [id]); 
                       } 
                    });
                }
            });
        },
        /* 新闻详情 */
        detail: function (args) {
            // entry news detail page
            scope.pageView = 'detail';
        
            // jq DOM
            var $loadingTip = $('#j_loading_tip'),      // 加载提示
                $scrollContent = $('#j_scroll_content'), // 滚动区域
                $commentsList = $('#j_comments_list');  // 评论列表
            // variable
            var newsDetailScop = '#j_news_detail', // 
                commentsListScop = '#j_comments_list', 
                getReadBtn = '#j_had_read',         // 已读btn
                getNotReadBtn = '#j_not_read';      // 未读btn
                
            // 获取新闻详情，绑定数据
            service.news.getNewsDetail({ id: args.params.id }).done(function (res) {
                if (res.code === 200) {
                    utils.bindDataToDOM(res.data.detail, 'news', newsDetailScop);
                }
            });

            // 显示已读列表
            utils.getHadReadMember({
                url: service.news.getHadReadList,
                param: { id: args.params.id },
                popBlast: getReadBtn
            });
            // 显示未读列表
            utils.getNotReadMember({
                url: service.news.getNotReadList,
                remindUrl: service.news.remindRead,
                param: { id: args.params.id },
                popBlast: getNotReadBtn
            });
            // 获取新闻评论
            utils.getComments({
                url: service.news.getComments,
                param: { id: args.params.id, offset: 0, limit: 15 },
                $tip: $loadingTip,
                $scrollContent: $scrollContent,
                success: function(res) {
                    utils.repeatDOM(res, 'comment', commentsListScop, true);
                },
                error: function(err) { CreatePop.warning(err) },
            });
            // 获取评论回复
            utils.getReply({
                url: service.news.getCommentReply,
                method: 'post',
                commentsList: $commentsList,
                delegate: '.j-get-reply-btn',
                limit: 5
            });
        }
    }
});