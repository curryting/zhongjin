$(function () {
    // jq DOM
    var $forgetBtn = $('#j_forget'),        // 按钮--忘记密码
        $cancelBtn = $('#j_cancel'),        // 按钮--取消操作
        $imgCode = $('.j-img-code'),        // 图片--验证码
        $phoneCode = $('#j_phone_code'),    // 按钮--发送手机验证码 
        $signForm = $('#j_sign_form'),      // 登录表单
        $forgetForm = $('#j_forget_form'),  // 忘记密码表单
        $sinInTab = $('#j_sign_in'),        // 登录选项卡
        $forgetTab = $('#j_forget_password'),// 忘记密码选项卡
        $tipModal = $('#j_tip_modal'),      // 提示框
        $newPwd = $('#j_new_pwd'),          // 新密码input
        $confirmPwd = $('#j_confirm_pwd');  // 确认密码input
    // varable
    var scope = {  // 存储数据
        signThrottle: true,
        forgetThrottle: true,
        confirmPwd: false,
    };
    // service
    // var host = 'http://localhost:2017';
    var service = {
        getImgCode: function () { return '/home/user/vcode' + '?' + new Date().getTime() },
        getPhoneCode: function (opt) { return $.post('/home/user/getPhoneCode', opt) },
        signIn: function (opt) { return $.post('/home/user/login', opt) },
        forgetKey: function (opt) { return $.post('/home/user/pwdFind', opt) }
    };
    // 切换到忘记密码
    $forgetBtn.on('click', function () {
        $sinInTab.slideUp(300).next().slideDown(300, function () {
            $(this).find('input').eq(0).focus();
        });
    }).on('click', function () {
        $imgCode.eq(1).attr('src', service.getImgCode()).prev().val('');
    });
    // 切换到登录
    $cancelBtn.on('click', function (eve) {
        eve.preventDefault();
        $forgetTab.slideUp(300).prev().slideDown(300, function () {
            $(this).find('input').eq(0).focus();
        });
        $imgCode.eq(0).trigger('click').prev().val('');
    });

    // 获取图片验证码
    $imgCode.on('click', function () {
        $(this).attr('src', service.getImgCode());
    });

    // 获取手机验证码
    $phoneCode.on('click', function () {
        var that = this;
        service.getPhoneCode({ username: $forgetForm.find('[name="username"]').val() }).done(function (res) {
            if (res.code === 200) {
                var timeout = 120;
                $(that).attr('disabled', true).html(timeout).addClass('disabled');
                // 倒计时函数
                function timeLeft() {
                    timeout--;
                    $(that).html(timeout)
                    if (timeout < 0) {
                        $(that).removeAttr('disabled').removeClass('disabled').html('发送验证码');
                        return;
                    }
                    setTimeout(arguments.callee, 1000);
                }
                // 开启倒计时
                setTimeout(timeLeft, 1000);
            } else {
                hasError(res.msg);
            }
        });
    });


    // 登录表单提交（登录和忘记密码公用表单）
    $signForm.on('submit', function () {
        clearInterval(scope.loginTimer);
        var _this = this;
        scope.loginTimer = setTimeout(function () {
            signInSubmit(_this);
        }, 100);
    });
    // 校验两次输入的密码
    $confirmPwd.on('blur', function () {
        if ($(this).val() === $newPwd.val()) {
            scope.confirmPwd = true;
            $(this).removeClass('has-error');
        } else {
            scope.confirmPwd = false;
            $(this).addClass('has-error').on('input', function () {
                if ($(this).val() === $newPwd.val()) {
                    scope.confirmPwd = true;
                    $(this).removeClass('has-error');
                } else {
                    $(this).addClass('has-error');
                    scope.confirmPwd = false;
                }
            });

        }
    });
    // 校验两次输入的密码
    $newPwd.on('blur', function () {
        if ($confirmPwd.val() !== '') {
            if ($(this).val() === $confirmPwd.val()) {
                scope.confirmPwd = true;
                $confirmPwd.removeClass('has-error');
            } else {
                $confirmPwd.addClass('has-error');
                scope.confirmPwd = false;
            }
        }
    });
    // 忘记密码提交表单
    $forgetForm.on('submit', function () {
        clearInterval(scope.forgetTimer);
        var _this = this;
        scope.forgetTimer = setTimeout(function () {
            forgetKeySubmit(_this);
        }, 100);
    });

    // 登录
    function signInSubmit(form) {
        if (scope.signThrottle) {
            var valid = verifyInput($(form).find('input'));
            if (valid) {
                scope.signThrottle = false;
                var formData = $(form).serialize();
                service.signIn(formData).done(function (res) {
                    if (res.code === 200) {
                        window.location.href = '/';
                        return;
                    } else {
                        hasError(res.msg);
                    }
                    scope.signThrottle = true;
                    $imgCode.eq(0).trigger('click').prev().val('');
                }).fail(function () {
                    hasError(xhr.statusText);
                    scope.signThrottle = true;
                });
            }
        }
    }
    // 忘记密码
    function forgetKeySubmit(form) {
        if (scope.forgetThrottle && scope.confirmPwd) {
            var valid = verifyInput($(form).find('input'));
            if (valid) {
                scope.forgetThrottle = false;
                var formData = $(form).serialize();
                service.forgetKey(formData).done(function (res) {
                    if (res.code == 200) {
                        // 获取当前用户名
                        var username = $forgetForm[0].username.value;
                        $signForm[0].username.value = username;
                        // 重置表单
                        $forgetForm[0].reset();
                        // 切换到登陆界面
                        $cancelBtn.click();
                        return;
                    } else {
                        hasError(res.msg);
                    }
                    scope.forgetThrottle = true;
                    $imgCode.eq(1).trigger('click').prev().val('');
                }).fail(function (xhr) {
                    hasError(xhr.statusText);
                    scope.forgetThrottle = true;
                });
            }
        }
    }

    // 错误提示
    function hasError(text) {
        var $activeNode = null;
        if ($sinInTab.is(':visible')) {
            $activeNode = $sinInTab;
        } else {
            $activeNode = $forgetTab;
        }
        $activeNode.addClass('has-error').find('.error-tip').text(text).end().parent().addClass('has-error');
        setTimeout(function () {
            $activeNode.removeClass('has-error').parent().removeClass('has-error');
        }, 2500)
    }
    // 校验输入是否合法
    function verifyInput($targets) {
        var illegal = true;
        var reg = null;
        $.each($targets, function (index, tar) {
            switch ($(tar).data('verify')) {
                case 'account':
                    reg = /^\S{3,}$/;
                    break;
                case 'password':
                    reg = /[\s\S]{5,}/;
                    break;
                case 'text':
                    reg = /\S/;
                    break;
                case 'phone':
                    reg = /^1[34578]\d{9}$/;
                    break;
                case 'imgcode':
                    reg = /^[A-Za-z0-9]{4}$/;
                    break;
                case 'email':
                    reg = /^[A-Za-z0-9\u4e00-\u9fa5]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/;
                    break;
                case 'phonecode':
                    reg = /^[0-9]{6}$/;
                    break;
                default:
                    reg = /\*/;
            }
            var flag = reg.test($(tar).val() || $(tar).text());
            if (!flag) {
                $(tar).addClass('has-error');
                setTimeout(function () {
                    $(tar).removeClass('has-error');
                }, 2000)
            }
            illegal = flag && illegal;
        });
        return illegal;
    }
});