define([
    'jquery',
    'service',
    'utils'
], function($, service, utils) {
    'use strict';
    
    // variable 
    var exportFn = {}, // 输出
        scope = { 
            treeExsit: false, // UI实例已存在
            categoryExsit: false,  // 目录结果已获取
            treeEvent: false, // 事件已绑定
            checkedBySearch: [], // 在搜索页选择的成员
            namesBySearch: [], // 在搜索页选择的成员的名字
            cates: [],  // 已加载过成员的目录
            result: { category: [], member: [], names: [] } // 最终选择的成员/目录结果
        }, // 数据暂存
        peakPid = -1,
        tempStr =
            '<div class="modal" id="j_select_object_ui">\
                <div class="modal-pop modal-send-to">\
                    <p class="title-center">选择发送对象</p>\
                    <div class="form-group">\
                        <form class="form-search" onsubmit="return false">\
                            <input type="search" id="j_search_memb_input" placeholder="请输入成员名称搜索">\
                            <i class="icon-search"></i>\
                        </form>\
                    </div>\
                    <div class="form-group hide">\
                        <form onsubmit="return false" class="form-check">\
                            <ul class="search-list" id="j_search_member_list_wrapper" tip="">\
                            </ul>\
                        </form>\
                    </div>\
                    <div class="form-group">\
                        <form onsubmit="return false" class="form-check" id="j_form_check">\
                            <div class="all-group">\
                                <input type="checkbox" name="category" data-name="全部成员" id="check_-1" value="-1">\
                                <label for="check_-1">全选</label>\
                            </div>\
                            <div class="group-list" id="j_select_obj_group_list_wrapper">\
                            </div>\
                        </form>\
                    </div>\
                    <div class="btn-group">\
                        <button class="btn-cancel" id="j_select_obj_cancal">取消</button>\
                        <button class="btn-theme" id="j_select_obj_sure">确定</button>\
                    </div>\
                </div>\
                <div class="modal-mask"></div>\
            </div>';
    // 挂载界面
    function mountInterface() {
        if (scope.treeExsit) { // 缓存ui
            return;
        }
        $('body').append(tempStr);
        scope.treeExsit = true;
        $.extend(scope, {
            $UI: $('#j_select_object_ui'),
            $searchInputId: $('#j_search_memb_input'),
            $checkAllBox: $('#check_-1'),
            $form: $('#j_form_check'),
            $groupWrapper: $('#j_select_obj_group_list_wrapper'),
            $searchList: $('#j_search_member_list_wrapper'),
            $cancelBtn: $('#j_select_obj_cancal'),
            $sureBtn: $('#j_select_obj_sure')
        });
    }
    // 挂载组
    function mountCategory() { 
        if (scope.categoryExsit) { // 缓存category
            return;
        }
        service.org.getStructure({ offset: -1 }).done(function(res) {
            if (res.code === 200) {
                var str = '',
                    list = res.data.list;
                for (var i = 0; i < list.length; i++) {
                    var checked = scope.result.category.indexOf(list[i].id) !== -1;
                    str += 
                    '<div class="group-wrapper">\
                        <div class="group-name">\
                            <i class="icon-triangle"></i>\
                            <input type="checkbox" name="category" value="'+ list[i].id +'" '+ (checked && 'checked') +' id="cate_'+ list[i].id +'" data-pid="'+ peakPid +'" data-name="'+ list[i].title +'">\
                            <label for="cate_'+ list[i].id +'">'+ list[i].title +'</label>\
                        </div>\
                        <ul class="member-list" id="j_member_list_'+ list[i].id +'"></ul>\
                    </div>';
                }
                scope.$groupWrapper.html(str);
                scope.categoryExsit = true;
            }
        });
    }
    // 挂载成员
    function mountMember(pid, isChecked) { // 不缓存member
        service.org.getMember({ id: pid }).done(function(res) {
            if (res.code === 200) {
                var str = '',
                    checkedLength = 0,
                    list = res.data.list;
                if (list.length) {
                    for (var i = 0; i < list.length; i++) {
                        var checked = isChecked || scope.checkedBySearch.indexOf(list[i].id) !== -1 || scope.result.member.indexOf(list[i].id) !== -1 || scope.checkedAll;
                        checked && checkedLength++;
                        str += '<li class="member">\
                                    <input type="checkbox" name="member" value="'+ list[i].id + '" '+ (checked && 'checked') +' id="memb_'+ pid + '_' + list[i].id +'" data-name="'+ list[i].name +'" data-pid="cate_'+ pid +'">\
                                    <label for="memb_'+ pid + '_' + list[i].id +'">'+ list[i].name +'</label>\
                                </li>';
                    }
                    // 父级选中
                    if (checkedLength === list.length) {
                        $('#j_member_list_' + pid).prev().find(':checkbox').attr('checked', true);
                    }
                    $('#j_member_list_' + pid).html(str).slideDown(); // member list
                } else {
                    $('#j_member_list_' + pid).prev().find('label').attr('nodata', 'true').prev().attr('disabled', true).removeAttr('checked');
                }
                scope.cates[pid] = true; // 标记
            }
        });
    }
    // 搜索成员（by phone or name）
    function searchMember(keyword) {
        if (keyword.trim()) { 
            service.org.search({ search: keyword.trim() }).done(function(res) {
                if(res.code === 200) { // res.data.list: [ {id: "26", name: "vedd", mobile: "13455566632"} ]
                scope.$searchList.empty();
                    var str = '',
                    list = res.data.list;
                    if (list.length) {
                        scope.$searchList.attr('tip', '');
                        for(var i = 0; i < list.length; i++) {
                            var checked = $('[name="member"][value="'+ list[i].id +'"]').is(':checked') || scope.checkedBySearch.indexOf(list[i].id) !== -1 || scope.checkedAll;;
                            str += '<li class="member">\
                                        <input type="checkbox" value="'+ list[i].id + '" '+ (checked || scope.checkedAll ? 'checked' : '') +' id="search_' + list[i].id +'" data-name="'+ list[i].name +'">\
                                        <label for="search_' + list[i].id +'">'+ list[i].name + '('+ list[i].mobile +')</label>\
                                    </li>';
                        }
                    } else {
                        scope.$searchList.attr('tip', '暂无结果');
                    }
                    scope.$searchList.html(str);
                    scope.$form.parent().addClass('hide').prev().removeClass('hide');
                    // scope.$sureBtn.text('返回');
                }
            });
        } else {
            scope.$form.parent().removeClass('hide').prev().addClass('hide');
        }
    }
    // 绑定 选中/搜索 事件
    function bindEvent(callback) {
        scope.sureBtnCallback = callback;
        if (scope.treeEvent) { // 只绑定一次
            return;
        }
        /* 勾选事件 */ 
        // 组 -- 全选/反选
        scope.$form.on('click', '.group-name :checkbox', function () {
            var $checkList = $(this).parent().next().find(':checkbox');
            // group-name 勾选
            if ($(this).is(':checked')) {
                $checkList.each(function (index, ele) {
                    ele.checked = 'checked';
                });
                var $checks = scope.$form.find('.group-list').find(':checkbox:not(:disabled)');
                // check_all -- 勾选			
                if ($checks.length === $checks.filter(':checked:not(:disabled)').length) {
                    scope.$checkAllBox[0].checked = 'checked';
                    scope.checkedAll = true;
                }
            } else {
                // group-name 取消勾选
                $checkList.removeAttr('checked')
                // check_all -- 取消勾选		
                scope.$checkAllBox.removeAttr('checked');
                scope.checkedAll = false;
            }
        });
        // 更新组选中状态/全选状态
        scope.$form.on('click', '.member-list :checkbox', function () {
            // group-name 取消勾选
            if (!$(this).is(':checked')) {
                $(this).parents('.member-list').prev().find(':checkbox').removeAttr('checked');
                // check_all -- 取消勾选	
                scope.$checkAllBox.removeAttr('checked');
                scope.checkedAll = false;
                // 取消勾选时，删除scope.checkedBySearch对应的项
                scope.checkedBySearch.forEach(function(ele, index, arr) {
                        this.value === ele && arr.splice(index, 1);
                }, this);
            }
            // group-name 勾选
            var $checks = $(this).parents('.member-list').find(':checkbox:not(:disabled)');
            if ($checks.length === $checks.filter(':checked:not(:disabled)').length) {
                $(this).parents('.member-list').prev().find(':checkbox')[0].checked = 'checked';
            } else {
                $(this).parents('.member-list').prev().find(':checkbox').removeAttr('checked');
                scope.$checkAllBox.removeAttr('checked');
                scope.checkedAll = false;
            }
            // check_all -- 勾选	
            if (scope.$form.find('.group-name').find(':checkbox:checked:not(:disabled)').length === scope.$form.find('.group-name').children('input:not(:disabled)').length) {
                scope.$checkAllBox[0].checked = 'checked';
                scope.checkedAll = true;
            } 
        });
        // 全选反选
        scope.$form.on('click', '.all-group :checkbox', function () {
            if ($(this).is(':checked')) {
                // checkbox 全部勾选
                scope.$form.find(':checkbox:not(:disabled)').each(function (index, ele) {
                    ele.checked = 'checked';
                });
                scope.checkedAll = true;
            } else {
                // checkbox 全部取消勾选
                scope.$form.find(':checkbox').removeAttr('checked');
                scope.checkedBySearch = [];
                scope.checkedAll = false;
            }
        });

        /* 搜索事件 */ 
        // 输入搜索
        scope.$searchInputId.on('input', function() {
           utils.throttle(searchMember, 500, [this.value]);
           
        }).on('keyup', function (eve) {
            if (eve.keyCode === 13 && this.value.trim()) {
                searchMember(this.value);
            }
        });
        // 点击搜索列表
        scope.$searchList.on('click', ':checkbox', function() {
            if (this.checked) { // 选中
                scope.checkedBySearch.push(this.value); 
                scope.namesBySearch.push($(this).data('name'));
            } else { // 取消选中
                var index = scope.checkedBySearch.indexOf(this.value);
                index !== -1 && scope.checkedBySearch.splice(index, 1);
                index !== -1 && scope.namesBySearch.splice(index, 1);
            }
            // 源列表触发点击事件
            scope.$groupWrapper.find('[name="member"][value='+ this.value +']').trigger('click');
        });

        // 显示折叠项
        scope.$groupWrapper.on('click', '.icon-triangle', function () {
            $(this).parent().toggleClass('active');
            $(this).parent('.active').next().stop().slideDown(300);
            $(this).parent(':not(.active)').next().stop().slideUp(300);
            var pid = $(this).next().attr('id').replace('cate_', ''),
                isChecked = $(this).next().is(':checked');
            if (scope.cates[pid]) { // 有 member 则不再获取
                return;
            } else {
                mountMember(pid, isChecked);
            }
        });

        // 点击取消，放弃勾选
        scope.$cancelBtn.on('click', function() { // 清空，不缓存 member list 
            scope.$UI.hide();
            $('#j_modal_mask').fadeOut(300);
        });
        
        // 点击确定，获取勾选项（export）
        scope.$sureBtn.on('click', function() {
            // if (scope.$form.parent().is(':visible')) { // 源列表
            scope.result = { names: [], category: [], member: [] }; // 清空result
            if (scope.$checkAllBox[0].checked) { // 全选
                scope.result = { names: ['全员'], category: [-1], member: [] };
            } else {
                scope.$form.serializeArray().forEach(function(ele, index, arr) {
                    switch(ele.name) {
                        case 'category':
                            scope.result.category.push(ele.value);
                            scope.result.names.push($('#cate_' + ele.value).data('name'));
                            break;
                        case 'member':
                            scope.result.member.push(ele.value);
                            scope.result.names.push($('[name="member"][value="'+ ele.value +'"]').data('name'));;
                            break;
                    }
                    
                }, this);
                scope.checkedBySearch.forEach(function(ele, index, arr) { // 搜索列表勾选的的成员
                    if (scope.result.member.indexOf(ele) === -1) {
                        scope.result.member.push(ele);
                        scope.result.names.push(scope.namesBySearch[index]);
                    } 
                }, this);
            }
            
            scope.$UI.hide();
            $('#j_modal_mask').fadeOut(300);
            scope.sureBtnCallback(scope.result);
            // } //else { // 搜索列表
                // scope.$form.parent().removeClass('hide').prev().addClass('hide');
                // scope.$sureBtn.text('确定');
            // }
        });
        // 显示界面（export）
        exportFn.showPop = function() {
            mountCategory(); // 获取并挂载目录
            $('#j_modal_mask').fadeIn(300);
            scope.$UI.show();
        }
        scope.treeEvent = true;
    }
    // 重置tree
    function resetTree() {
        scope.$checkAllBox.removeAttr('checked');
        scope.$groupWrapper.find('.group-name').removeClass('active')
            .find(':checkbox').removeAttr('checked').end()
            .next('.member-list').html('');
        scope.$searchInputId.val('');
        scope.$form.parent().removeClass('hide').prev().addClass('hide');
        
        scope.cates = [];
        scope.checkedBySearch = [];
        scope.namesBySearch = [];
        scope.result = { category: [], member: [], names: [] } ;
    }
    // 设置数据
    exportFn.setData = function (sendObj) {
        scope.result = $.extend({ category: [], member: [], names: [] }, sendObj);
        scope.checkedBySearch = sendObj.member || [];
        if (scope.categoryExsit) {
            scope.result.category.forEach(function(ele, index) {
                $('#cate_' + ele).click();
            });
        }
    }
    // 初始化tree
    exportFn.init = function (callback) {  
        mountInterface(); // 挂载UI
        bindEvent(callback); // 绑定事件callback获取选择的对象
        resetTree(); // 重置tree（除目录外）
    }
    return exportFn;

});