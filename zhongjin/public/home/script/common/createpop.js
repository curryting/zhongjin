define(['jquery'], function ($) {
    'use strict';


    /**
     *  <script>
     *      // 快捷操作弹窗(tip,warning,error隐藏后自动清除)
     *      CreatePop.tip('提示信息');
     *      CreatePop.warning('警告信息');
     *      CreatePop.error('错误信息');
     *      CreatePop.confirm('确认操作', sureCallBack);
     * 
     *      // 新建弹窗(可链式回调)
     *      var option = {type: 'confirm', name: 'danger', title: '警告', content: '您确定要执行此操作么？'};
     *      var pop = new CreatePop(option).sure(callFnA).cancel(callFnB)
     *                  .beforeShow(callFnC).afterShow(callFnD)
     *                  .beforeHide(callFnE).afterHide(callF);
     *      
     *      // 立即执行函数(callFn可选)
     *      pop.show(callFn1); // 显示
     *      pop.hide(callFn2); // 隐藏
     *      pop.afterMounted(callFn3); // 挂载后立即执行callFn3
     *      pop.removePop(callFn4); // 移除当前pop
     *      pop.setTlite('xxxx');  // pop挂载后可以重新设置标题
     *  </script>
     *  其他说明
     *  1.代理弹窗
     *  eg-------------------------------------------------------------------
     * html:<tbody id="j_delegate_pop"> 
     *          <tr> <td class="j-blast-pop1">编辑</td> <td class="j-blast-pop2">删除</td> </tr> 
     *      </tbody>
     * js:  new CreatePop({
     *          type: 'confirm',
     *          name: 'danger',
     *          title: 'titlexxx',
     *          content: 'contentxxx',
     *          blast: '.j-blast-pop1', 
     *          delegate: '.j_delegate_pop',
     *          xxxCallBack: xxx,
     *          yyyCallBack: yyy,
     *          ......
     *      });
     *  -------------------------------------------------------------------eg
     * 2.弹窗事件
     *  弹窗由点击事件触发，返回 click event 就是 popEvent，
     *  获取 event: pop.popEvent  [pop 是CreatePop 的实例]
     * 
     * 3.阔展弹窗
     *  在CreatePop.prototype的popSnippet 对象中添加相应的 html片段，css 写在 modal.less文件中
     * 
     * 弹出模态框
     * @param    {string}       popID               // 弹出框id [可选],jquery选择器
     * @param    {string}       type                // 弹出框的类型 [必要]
     * @param    {string}       name                // 弹出框的名称 [必要]
     * @param    {string}       title               // 弹出框的标题 [必要]
     * @param    {string}       content             // 弹出框要显示的内容 [必要]
     * @param    {string}       blast               // 触发弹窗显示的dom，jquery选择器(代理的时候建议使用class) [可选]
     * @param    {string}       delegate            // 代理触发弹窗dom，jquery选择器 [可选]
     * @param    {Boolean}      autoClear           // 操作结束后是否自动移除弹框DOM [false] [可选]
     * @param    {Boolean}      autoHide            // 操作结束后是否自动隐藏弹框DOM [true] [可选]
     * @param    {String}       sureBtn             // 确定按钮文字 [可选]
     * @param    {String}       cancelBtn           // 取消按钮文字 [可选]
     * @param    {function}     beforeShowCallBack  // 弹出框显示前执行回调函数 [可选]	
     * @param    {function}     afterShowCallBack   // 弹出框显示后执行的回调函数 [可选]	
     * @param    {function}     beforeHideCallBack  // 弹出框隐藏前执行回调函数 [可选]	
     * @param    {function}     afterHideCallBack   // 弹出框隐藏后执行的回调函数 [可选]	
     * @param    {function}     sureCallBack        // 弹出框的确定回调函数 [可选]	
     * @param    {function}     cancelCallBack      // 弹出框的取消回调函  [可选]
     * @param    {function}     afterMountCallBack  // 弹出框的html挂载后要执行的函数 [可选] 
     */

    function CreatePop(opt) {
        opt = opt || {}
        if (this instanceof CreatePop) {
            // 初始化参数
            this._initOpt(opt);
            // 挂载DOM
            this._mountDOM();
            // 绑定事件
            this._bindEvent();
        } else {
            return new CreatePop(opt);
        }
    }
    CreatePop.prototype = {
        // 初始化参数
        _initOpt: function (opt) {
            this.type = opt.type || 'alert';
            this.name = opt.name || 'info';
            this.title = opt.title || '提示';
            this.content = opt.content || 'Hello World!';
            this.blast = opt.blast || '';
            this.delegate = opt.delegate || '';
            this.autoClear = opt.autoClear || false;
            this.autoHide = opt.autoHide === false ? false : true;
            this.sureBtn = opt.sureBtn || '确定';
            this.cancelBtn = opt.cancelBtn || '取消';
            this.beforeShowCB = opt.beforeShowCallBack || null;
            this.afterShowCB = opt.afterShowCallBack || null;
            this.beforeHideCB = opt.beforeHideCallBack || null;
            this.afterHideCB = opt.afterHideCallBack || null;
            this.sureCB = opt.sureCallBack || null;
            this.cancelCB = opt.cancelCallBack || null;
            this.afterMountCB = opt.afterMountCallBack || null;
            // this.createTime = new Date().getTime();
            this.popID = opt.popID ? (opt.popID + Math.random()).replace('0.', '') : ('#' + Math.random()).replace('0.', ''); // jquery id
            this.normalID = this.popID.slice(1); // dom id
            this.popEvent = null; // 触发弹窗的click event
            this.$element = null; // 实例创建后，获取到该实例DOM对象
            this.formID = null;   // pop中第一个form的 id，jquery选择器
            this.$form = null;    // pop中第一个form，jquery选择器
            this.isExist = false; // 记录该实例是否已经存在
            this.popSign = (this.type + this.name + this.title + this.blast + this.delegate + opt.popID).replace('.', '').replace(' ', ''); // 该弹窗的标志
            // 在统计数组中查询该弹窗是否存在
            CreatePop.idArray.forEach(function (ele, index, arr) {
                if (ele.popSign === this.popSign) {
                    this.isExist = true;
                    // 对于设置了自动清除的pop，它的id随机生成，统计数组中应该清除它原来的id
                    if (this.autoClear) {
                        arr.splice(index, 1);
                    } else {
                        this.popID = ele.popID
                    }
                }
            }, this);
        },
        // 挂载DOM
        _mountDOM: function () {
            // 生成遮罩层
            $('#j_modal_mask').length || $('body').append('<div class="modal-mask" id="j_modal_mask"></div>');
            // 判断该弹窗是否存在，若不存在或是设置自动清除，则挂载popDOM
            if (!this.isExist || this.autoClear) {
                var popHTML = this.popSnippet[this.type][this.name](this);
                $('body').append(popHTML);
                // 将该弹窗的标志添加到数组
                CreatePop.idArray.push({ popSign: this.popSign, popID: this.popID })
            }
            this.$element = $(this.popID);
            this.$form = this.$element.find('form');
            if (this.$form[0]) {
                this.$form[0].id = 'j_form' + this.normalID;
                this.formID = '#j_form' + this.normalID;
            }
            if (typeof this.afterMountCB === 'function')
                this.afterMountCB();
            return this;
        },
        // 绑定事件
        _bindEvent: function () {
            var _this = this;
            // 弹出模态框
            if (this.blast) {
                // 判断是否使用了代理
                if (this.delegate) {
                    $(this.delegate).on('click', _this.blast, function (eve) {
                        _this.popEvent = eve;
                        _this.show();
                    });
                } else {
                    $(this.blast).on('click', function (eve) {
                        _this.popEvent = eve;
                        _this.show();
                    });
                }
            }

            // 点击取消按钮
            $('#0' + this.normalID).on('click', function (eve) {
                _this.popEvent = eve;
                _this.hide();
                _this.cancelCB && _this.cancelCB()
            });

            // 点击确定按钮
            $('#1' + this.normalID).on('click', function (eve) {
                _this.popEvent = eve;
                _this.autoHide && _this.hide();
                _this.sureCB && _this.sureCB();
            });
        },
        // 取消操作
        cancel: function (fn) {
            if (typeof fn !== 'function')
                return this;
            this.cancelCB = fn;
            return this;
        },
        // 确认操作
        sure: function (fn) {
            if (typeof fn !== 'function')
                return this;
            this.sureCB = fn;
            return this;
        },
        // 挂载后的回调
        afterMounted: function (fn) {
            if (typeof fn !== 'function')
                return this;
            this.afterMountCB = fn;
            this.afterMountCB();
            return this;
        },
        // 移除DOM
        removePop: function (fn) {
            var _this = this;
            setTimeout(function () {
                $(_this.popID).remove();
                typeof fn === 'function' && fn();
            }, 0);
            return this;
        },
        // 模态框显示之前执行
        beforeShow: function (fn) {
            this.beforeShowCB = fn;
            return this;
        },
        // 显示弹框
        show: function (fn, time) {
            this.beforeShowCB && this.beforeShowCB()
            $('#j_modal_mask').fadeIn(300);
            $(this.popID).fadeIn(time || 100, fn || this.afterShowCB);
            return this;
        },
        // 模态框显示之后执行
        afterShow: function (fn) {
            this.afterShowCB = fn;
            return this;
        },
        // 模态框隐藏之前执行
        beforeHide: function (fn) {
            this.beforeHideCB = fn;
            return this;
        },
        // 隐藏弹框
        hide: function (fn, time, delay) {
            setTimeout(function() {
                this.beforeHideCB && this.beforeHideCB()
                $(this.popID).fadeOut(time || 300, fn || this.afterHideCB);
                $('#j_modal_mask').fadeOut(150);
                this.autoClear && this.removePop();
            }.bind(this), delay || 0);
            return this;
        },
        // 模态框隐藏之后执行
        afterHide: function (fn) {
            this.afterHideCB = fn;
            return this;
        },
        setTitle: function (text) {
            this.title = text;
            $(this.popID).find('.title-center').html(text);
            return this;
        },
        setSureBtn: function (text) {
            this.sureBtn = text;
            $('button#1' + this.normalID).html(text);
            return this;
        },
        setCancelBtn: function (text) {
            this.cancelBtn = text;
            $('button#0' + this.normalID).html(text);
            return this;
        },
        // 弹框的HTML片段
        popSnippet: {
            // 警告
            alert: {
                success: '',
                info: function (_this) {
                    return _this.popSnippet.alert.temp(_this);
                },
                warning: function (_this) {
                    return _this.popSnippet.alert.temp(_this);
                },
                danger: function (_this) {
                    return _this.popSnippet.alert.temp(_this);
                },
                temp: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">\
                                <div class="modal-pop alert ' + _this.name + '">\
                                    <div class="tips">\
                                        <span class="icon"></span>\
                                        <span class="text">' + _this.content + '</span>\
                                    </div>\
                                </div>\
                            </div>';
                }
            },
            // 确认
            confirm: {
                info: function(_this) {
                    return _this.popSnippet.confirm.danger(_this);
                },
                warning: function (_this) {
                    return _this.popSnippet.confirm.danger(_this);
                },
                // 警告操作
                danger: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">\
                                <div class="modal-pop modal-confirm ' + _this.name + '">\
                                    <div class="tips">\
                                        <span class="icon"></span>\
                                        <span class="text">' + _this.content + '</span>\
                                    </div>\
                                    <div class="btn-group">\
                                        <button class="btn-cancel" id="0' + _this.normalID + '">取消</button>\
                                        <button class="btn-theme-o" id="1' + _this.normalID + '">确定</button>\
                                    </div>\
                                </div>\
                            </div>';
                }
            },
            // 输入
            prompt: {
                // 修改密码
                resetPass: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-reset-password">'
                        + '<p class="title-center">' + _this.title + '</p>'
                        + '<form onsubmit="return false">'
                        + '<div class="form-group">'
                        + '<label>原密码：</label>'
                        + '<input type="password" name="old_pass" placeholder="请输入旧密码" autocomplete="off" required rangelength="3, 16">'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>新密码：</label>'
                        + '<input type="password" name="new_pass" id="j_new_pass" placeholder="请输入新密码" autocomplete="off" required rangelength="5, 16">'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>确认密码：</label>'
                        + '<input type="password" name="confirm_pass" placeholder="请再次输入密码" autocomplete="off" required equalTo="#j_new_pass">'
                        + '</div>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme" id="1' + _this.normalID + '">' + _this.sureBtn + '</button>'
                        + '</div>'
                        + '</form>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 添加客户
                addClient: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-add-client">'
                        + '<p class="title-center">' + _this.title + '</p>'
                        + '<form onsubmit="return false">'
                        + '<input type="hidden" name="id">'
                        + '<div class="form-group">'
                        + '<label>客户类型：</label>'
                        + '<input type="radio" name="client_type" value="1" id="person' + _this.normalID + '" checked="checked">'
                        + '<label class="check-tag" for="person' + _this.normalID + '">个人客户</label>'
                        + '<input type="radio" name="client_type" value="2" id="org' + _this.normalID + '">'
                        + '<label class="check-tag" for="org' + _this.normalID + '">机构客户</label>'
                        + '</div>'
                        + '<div class="form-group hide" id="j_company' + _this.normalID + '">'
                        + '<label class="spacing">公司名称：</label>'
                        + '<input type="text" name="company_name" placeholder="请输入公司名称" disabled="true" autocomplete="off" required rangelength="3, 32">'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label class="spacing">姓       名：</label>'
                        + '<input type="text" name="name" placeholder="请输入姓名" autocomplete="off" required rangelength="2, 20">'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>证件类型：</label>'
                        + '<select name="id_type" required>'
                        + '<option value="">请选择</option>'
                        + '<option value="1">身份证</option>'
                        + '<option value="2">营业执照</option>'
                        + '<option value="3">港澳通行证</option>'
                        + '<option value="4">护照</option>'
                        + '<option value="5">台胞回乡证</option>'
                        + '<option value="6">其它</option>'
                        + '</select>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>证件号码：</label>'
                        + '<input type="text" name="id_number" placeholder="请输入证件号码" autocomplete="off" required maxlen="32">'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>手机号码：</label>'
                        + '<input type="number" name="phone" placeholder="请输入手机号码" autocomplete="off" required number min="13000000000" max="18999999999">'
                        + '</div>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme" id="1' + _this.normalID + '">' + _this.sureBtn + '</button>'
                        + '</div>'
                        + '</form>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 转让客户列表
                transfer: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">\
                        <div class="modal-pop modal-transfer">\
                            <div class="title-center">'+_this.title+'</div>\
                            <form onsubmit="return false">\
                                <div class="form-group">\
                                    <input type="search" name="search" id="j_search_input" placeholder="输入客户名或手机号搜索">\
                                </div>\
                                <div class="form-group origin-list">\
                                    <ul class="client-list" id="j_origin_list">\
                                        <li repeat-dom="client">\
                                            <input name="to" type="radio" id="origin{client.id}" value="{client.id}" required>\
                                            <label for="origin{client.id}">{client.name}({client.mobile})</label>\
                                        </li>\
                                    </ul>\
                                </div>\
                                <div class="form-group search-list">\
                                    <ul class="client-list hide" id="j_search_list">\
                                        <li repeat-dom="client">\
                                            <input name="to" type="radio" id="search{client.id}" value="{client.id}">\
                                            <label for="search{client.id}">{client.name}({client.mobile})</label>\
                                        </li>\
                                    </ul>\
                                </div>\
                            </form>\
                            <div class="btn-group">\
                                <button class="btn-cancel" id="0' + _this.normalID + '">取消</button>\
                                <button class="btn-theme" id="1' + _this.normalID + '">确定</button>\
                            </div>\
                        </div>\
                    </div>'
                },
                // 添加投资
                addInvest: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-add-invest">'
                        + '<div class="title-center">' + _this.title + '</div>'
                        + '<form onsubmit="return false">'
                        + '<div class="form-group">'
                        + '<label class="spacing">客       户：</label>'
                        + '<input name="id" type="hidden" bind-data="{clientInfo.id}">'
                        + '<input type="text" name="client_name" bind-data="{clientInfo.name}" disabled="true" required>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>投资产品：</label>'
                        + '<select name="product" required>'
                        + '<option value="">选择投资产品</option>'
                        + '<option value="{product.id}" repeat-dom="product">{product.title}</option>'
                        + '</select>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>开户银行：</label>'
                        + '<input type="text" name="bank_name" required>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>银行账号：</label>'
                        + '<input type="number" name="bank_account" number required>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>投资金额：</label>'
                        + '<input type="text" name="money" required>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>投资日期：</label>'
                        + '<input type="text" class="time-input" name="invest_date" readonly="true" required>'
                        + '</div>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme" id="1' + _this.normalID + '">' + _this.sureBtn + '</button>'
                        + '</div>'
                        + '</form>'
                        + '<div class="info">'
                        + '<p bind-class="{clientInfo.company_name ? \'show\' : \'hide\'}"><span>公司名称：</span><span bind-data="{ clientInfo.company_name }"></span></p>'
                        + '<p><span>手机号码：</span><span bind-data="{ clientInfo.phone }"></span></p>'
                        + '<p><span>证件类型：</span><span bind-data="{ clientInfo.id_type }"></span></p>'
                        + '<p><span>证件号码：</span><span bind-data="{ clientInfo.id_number }"></span></p>'
                        + '</div>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 添加投资报告
                addInvestReport: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-add-invest-report">'
                        + '<p class="title-center">' + _this.title + '</p>'
                        + '<form onsubmit="return false" enctype="multipart/form-data">'
                        + '<div class="form-group">'
                        + '<label>报告类型：</label>'
                        + '<input type="radio" name="report_type" value="1" id="report_type_a" checked="checked">'
                        + '<label class="check-tag" for="report_type_a">投后报告</label>'
                        + '<input type="radio" name="report_type" value="2" id="report_type_b">'
                        + '<label class="check-tag" for="report_type_b">合规文件</label>'
                        + '<input type="radio" name="report_type" value="3" id="report_type_c">'
                        + '<label class="check-tag" for="report_type_c">基金合同</label>'
                        + '</div>'
                        + '<div class="form-group">'
                        + '<label>投资报告：</label>'
                        + '<a class="upload-file">选择文件</a>'
                        + '<div class="file-name">'
                        + '<div class="text-name j-upload-filename"></div>'
                        + '<span class="del">×</span>'
                        + '</div>'
                        + '</div>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme" id="1' + _this.normalID + '">' + _this.sureBtn + '</button>'
                        + '</div>'
                        + '</form>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 批量导入
                import: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-import-client">'
                        + '<p class="title-center">' + _this.title + '</p>'
                        + '<form onsubmit="return false" enctype="multipart/form-data">'
                        + '<div class="form-group">'
                        + '<label>上传附件：</label>'
                        + '<a class="upload-file" title="支持xls，xlsx文件">选择文件</a>'
                        + '<a href="/dl/import_customer.xlsx" class="download j-template-url" download="批量导入客户模板.xlsx">下载附件模板</a>'
                        + '<div class="file-name">'
                        + '<div class="text-name j-upload-filename"></div>'
                        + '<span class="del">×</span>'
                        + '</div>'
                        + '</div>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme"  id="1' + _this.normalID + '">' + _this.sureBtn + '</button>'
                        + '</div>'
                        + '</form>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 未读提醒
                hadNotRead: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-had-not-read">'
                        + '<p class="title-center">' + _this.title + '</p>'
                        + '<ul class="lists">'
                        + '<li class="item" repeat-dom="notRead" title="{notRead.name}">{notRead.name}</li>'
                        + '</ul>'
                        + '<p class="tip"></p>'
                        + '<div class="btn-group">'
                        + '<button class="btn-theme" id="1' + _this.normalID + '">一键提醒</button>'
                        + '</div>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 已读提醒
                hadRead: function (_this) {
                    return '<div class="modal" id="' + _this.normalID + '">'
                        + '<div class="modal-pop modal-had-read">'
                        + '<p class="title-center">' + _this.title + '</h3>'
                        + '<ul class="lists">'
                        + '<li class="item" repeat-dom="hadRead" title="{hadRead.name}">{hadRead.name}</li>'
                        + '</ul>'
                        + '<p class="tip"></p>'
                        + '<span class="close" id="0' + _this.normalID + '"></span>'
                        + '</div>'
                        + '</div>';
                },
                // 定时发送
                clocking: function(_this) {
                   return '<div class="modal" id="' + _this.normalID + '">\
                            <div class="modal-pop modal-clocking">\
                                <p class="title-center">' + _this.title + '</p>\
                                <form onsubmit="return false">\
                                    <div class="form-group">\
                                        <input type="text" name="clocking" class="time-input" id="j_clocking_input" readonly required>\
                                    </div>\
                                    <div class="btn-group">\
                                        <button class="btn-cancel" type="button" id="0' + _this.normalID + '">' + _this.cancelBtn + '</button>\
                                        <button class="btn-theme" type="submit" id="1' + _this.normalID + '">' + _this.sureBtn + '</button>\
                                    </div>\
                                </form>\
                            </div>\
                        </div>';
                }
            }
        }
    }
    // CreatePop快捷方法
    // 提示弹窗
    CreatePop.tip = function (message, delay) {
        return new CreatePop({
            type: 'alert',
            name: 'info',
            content: message,
            autoClear: true,
        }).show().hide(null, 300, 1500);;
    }
    // 警告弹窗
    CreatePop.warning = function (message, delay) {
        return new CreatePop({
            type: 'alert',
            name: 'warning',
            content: message,
            autoClear: true,
        }).show().hide(null, 300, 2500);
    }
    // 错误信息弹窗
    CreatePop.error = function (message, delay) {
        return new CreatePop({
            type: 'alert',
            name: 'danger',
            content: message,
            autoClear: true,
        }).show().hide(null, 300, 3000);;
    }
    // 确认操作
    CreatePop.confirm = function (name, message, sureCallBack) {
        return new CreatePop({
            type: 'confirm',
            name: name || 'info',
            // autoHide: true,
            content: message || '信息',
            sureCallBack: sureCallBack
        }).show();
    }
    CreatePop.idArray = [{ popID: '', popSign: '' }];
    $(document).on('keyup', function (eve) { eve.keyCode === 27 && $('.modal:visible').eq(0).hide(); })
    return CreatePop;
});