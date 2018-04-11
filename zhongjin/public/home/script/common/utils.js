define(['jquery', 'DAO'], function ($, DAO) {
    'use strict';
    // data 
    var scope = {};
    // jquery subscribe / publish plugin
    (function ($) {
        var o = $({});
        $.subscribe = function () {
            o.on.apply(o, arguments);
        };
        $.unsubscribe = function () {
            o.off.apply(o, arguments);
        };
        $.publish = function () {
            o.trigger.apply(o, arguments);
        };
    }(jQuery));

    /************* 公共函数 *************/
    var utils = {
      
        /**
         * 绑定数据到DOM，使用$(xx).html(val), $(xxx).val(val)赋值，可以绑定类名
         * eg ----------------------------------------------------------------
         * html:    <span bind-data="{prefix.key}"> </span> 
         *          <span bind-class="{prefix.name ? 'show' : 'hide'}"> xxxx </span>
         *          <span bind-html="{prefix.html}"></span>
         *          <a bind-href="{prefix.href}"></a>
         *          <ul><li>{prefix._index}</li></ul> 索引
         * js: <script> utils.bindDataToDOM(obj, 'prefix', '#j_load_container') </script>
         * ---------------------------------------------------------------- eg
         * @param    {Object}       obj             要绑定的对象 [必要]
         * @param    {string}       prefix          bind-data的前缀 [必要] 如：bind-data="{prefix.key}"
         * @param    {string}       context         目标所在的上下文 [可选]（jquery选择器）
         * */
        bindDataToDOM: function (obj, prefix, context) {
            if (obj === null) {
                return;
            }
            if (typeof obj !== 'object' || prefix === undefined) {
                console.error('参数错误！绑定数据失败!');
                return false;
            }
            // 模板符号
            var regex = new RegExp('\{(\\s*' + prefix + '\\..*?)\}', 'g');
            // 获取上下文
            if (typeof context === 'string') {
                var $context = $(context).length ? $(context) : $('body');
            } else if (typeof context === 'object') {
                var $context = context;
            }
            // 缓缓选jquery择器
            var $tag = null;
            // 绑定类名
            $context.find('[bind-class*="' + prefix + '."]').each(function (index, tag) {
                var exp = $(tag).attr('bind-class').replace(new RegExp(prefix, 'g'), 'obj');
                // 获取类名
                var val = eval(exp);
                // 判断是隐藏还是显示
                val === 'show' && $(tag).removeClass('hide');
                val === 'hide' && $(tag).removeClass('show');
                $(tag).addClass(val);
            });
            // 绑定href
            $context.find('[bind-href*="' + prefix + '."]').each(function (index, tag) {
                var exp = $(tag).attr('bind-href').replace(new RegExp(prefix, 'g'), 'obj');
                var val = eval(exp);
                $(tag).attr('href', val);
            });
            // 绑定html片段
            $context.find('[bind-html*="' + prefix + '."]').each(function (index, tag) {
                var exp = $(tag).attr('bind-html').replace(new RegExp(prefix, 'g'), 'obj');
                var val = eval(exp);
                $(tag).html(val);
            });
            // 获取属性bind-data包含 prefix. 的DOM，并遍历，进行赋值操作
            $context.find('[bind-data*="' + prefix + '."]').each(function (index, tag) {
                // 获取到属性值为 {prefix.key}，并替换为 {obj.key}
                $tag = $(tag);
                var exp = $tag.attr('bind-data').replace(new RegExp(prefix, 'g'), 'obj');
                // 判断DOM类型进行赋值
                try {
                    switch ($tag.prop('nodeName')) {
                        default:
                            $tag.text(eval(exp));
                            break;
                        case 'INPUT':
                            $tag.val(eval(exp));
                            break;
                        case 'SELECT':
                            $tag.val(eval(exp));
                            break;
                        case 'TEXTAREA':
                            $tag.val(eval(exp));
                            break;
                        case 'IMG':
                            $tag.attr('src', eval(exp));
                            break;
                    }
                } catch (e) {
                    console.error(e)
                }
            });
        },
        /**
         * 从DOM中获取数据，bindDataToDOM 的逆向操作，返回对象
         */
        getDataFromDOM: function (prefix, context) {
            if (prefix === undefined) {
                console.error('参数错误！获取数据失败!');
                return false;
            }
            // 保存取值
            var obj = {};
            // 缓缓选jquery择器
            var $tag = null;
            // 获取上下文
            var $context = $(context).length ? $(context) : $('body');
            // 获取属性bind-data包含 prefix. 的DOM，并遍历，进行取值操作
            $context.find('[bind-data*="' + prefix + '."]').each(function (index, tag) {
                // 获取到属性值为 {prefix.key}
                $tag = $(tag);
                var exp = $tag.attr('bind-data');
                // {prefix.key} 替换为 key
                exp = exp.replace(prefix + '.', '').replace('{', '').replace('}', '').trim();
                // 判断DOM类型进行赋值
                switch ($tag.prop('nodeName')) {
                    default:
                        obj[exp] = $tag.html().trim();
                        break;
                    case 'INPUT':
                        obj[exp] = $tag.val().trim();
                        break;
                    case 'SELECT':
                        obj[exp] = $tag.val().trim();
                        break;
                    case 'TEXTAREA':
                        obj[exp] = $tag.val().trim();
                        break;
                }
            });
            return obj;
        },
        /**
         *  通过数组循环生成DOM, 同时设置了reserveTemp true position replace 需设置 reserveNum
         * * eg ----------------------------------------------------------------
         * html: <div repeat-dom="prefix">{ prefix.key1 }  <input value={prefix.key2} /> <img data-src="{prefix.src}"/>  </div> 
         * js:   <script> utils.repeatDOM(arrObj, 'prefix', '#j_load_container')   </script>
         * ---------------------------------------------------------------- eg
         * @param    {Array}        arrObj          要遍历的数组 [必要]
         * @param    {String}       prefix          repeat-dom 的前缀 [必要] 如：repeat-dom="prefix"
         * @param    {String}       context         目标所在的上下文 [可选]（jquery选择器）
         * @param    {Boolean}      reserveTemp     是否保留循环模板 false | true
         * @param    {String}       position        插入父级元素的位置， append | prepend | replace
         * @param    {Number}       reserveNum        要保存多少个子元素
         */
        repeatDOM: function (arrObj, prefix, context, reserveTemp, position, reserveNum) {
            if (!(arrObj instanceof Array) || prefix === undefined) {
                return console.error('参数错误，无法遍历DOM！');
            }
            // 默认插入位置
            position = position || 'bottom';
            // 存储最终结果字符串
            var result = '';
            // 用于匹配 {} 模板
            var regex = new RegExp('\{(\\s*' + prefix + '\\..*?)\}', 'g');
           
            // 获取上下文
            var $context = null;
            if (typeof context === 'string') {
                $context = $(context).length ? $(context) : $('body');
            } else if (typeof context === 'object') {
                $context = context;
            }
            // 循环体
            var $loopNode = $context.find('[repeat-dom="' + prefix + '"]');
            // 循环体父级DOM
            var $parentNode = $loopNode.parent();
            // 保留循环模板
            if (reserveTemp) {
                $loopNode = $loopNode.clone();
            }
            // 临时DOM用于转换DOM为字符串
            var $tempDOM = $('<div></div>').append($loopNode.removeAttr('repeat-dom'));
            // 取到循环体为字符串
            var nodeStr = $tempDOM.html();
            // 循环遍历传入的数组
            for (var i = 0; i < arrObj.length; i++) {
                // 要循环匹配循环体
                var str = nodeStr;
                // 匹配结果为null或数组
                var arr = null;
                // 匹配次数
                var count = 0;
                // 循环匹配模板
                while ((arr = regex.exec(str)) != null) {
                    // 重新从索引号0开始查找
                    regex.lastIndex = 0;
                    // 匹配结果 {xxx.key}
                    var tempReg = new RegExp(arr[0].replace(/\./g, '\\.').replace(/\?/g, '\\?').replace(/\[/g, '\[').replace(/\]/g, '\]').replace(/\(/g, '\(').replace(/\)/g, ('\)')));
                    // 得到key
                    if (typeof arrObj[i] === 'object') {
                        // 设置索引值
                        arrObj[i]._index = arrObj[i]._index ? arrObj[i]._index : i;
                        // 转换表达式
                        var exp = arr[1].trim().replace(new RegExp(prefix, 'g'), 'arrObj[' + i + ']');
                        // 全部替换为 arrObj[i].key]
                        str = str.replace(tempReg, eval(exp));
                    } else {
                        var exp = arrObj[i];
                        str = str.replace(tempReg, exp);
                    }
                    // 防止死循环
                    if (++count > 100) { break; }
                }
                // 结果累加
                result += str;
            }
            // 添加到页面相应的位置
            position === 'top' && $parentNode.prepend(result);
            position === 'bottom' && $parentNode.append(result);
            if (position === 'replace') {
                if (reserveTemp) {
                    $parentNode.children(':gt('+ (reserveNum - 1) +')').remove();
                    $parentNode.append(result);
                } else {
                    $parentNode.html(result);
                }
            }
              
            // 图片加载bug
            $parentNode.find('img[data-src]').each(function(index, ele) {
                var src = $(ele).data('src');
                if (src.indexOf('{') === -1 && !ele.src) {
                    ele.src = src;
                }
            }); 
        },

        /**
         * 获取表单的值并返回 object
         * @param    {string}       form      form表单 [jquery选择器]
         * @returns  {object}       
         */
        getFormData: function (form) {
            var $form = null,
                obj = {};
            if (typeof form === 'string') {
                $form = $(form);
            } else if(typeof form === 'object') {
                $form = form;
            }
            $form.serializeArray().forEach(function (ele, index, arr) {
                if (obj[ele.name] instanceof Array) { // 键name已存在，且为数组
                    // push value
                    obj[ele.name].push(ele.value);
                } else if (typeof obj[ele.name] === 'string') { // 键name已存在，转为数组
                    // parse obj to array
                    var tmpArr = new Array(obj[ele.name]);
                    tmpArr.push(ele.value);
                    obj[ele.name] = tmpArr;
                } else {
                    obj[ele.name] = ele.value;
                }
            }, this);
            return obj;
        },
        /**
         * 给表单赋值
         * @param       {String}        id      // form 表单的id,jquery选择器 [必要]
         * @param       {Object}        obj     // 源数据对象 [必要]
         * 说明：
         *      确保表单有name值，且name值不重复(radio除外)
         */
        setFormData: function (id, obj) {
            if ($(id).length && typeof obj === 'object') {
                var $tag = null;
                for (var key in obj) {
                    $tag = $(id).find('[name="' + key + '"]');
                    if ($tag.length) {
                        switch ($tag.prop('nodeName')) {
                            case 'INPUT':
                                switch ($tag.attr('type')) {
                                    case 'radio':
                                        $tag.each(function (index, ele) {
                                            ele.value == obj[key] && (ele.checked = 'checked');
                                        })
                                        break;
                                    case 'checkbox':
                                        $tag.each(function (index, ele) {
                                            if (obj[key].indexOf(ele.value) !== -1 || obj[key].indexOf(parseInt(ele.value)) !== -1) {
                                                ele.checked = 'checked'
                                            } else {
                                                $(ele).removeAttr('checked');
                                            };
                                        });
                                        break;
                                    default:
                                        $tag.val(obj[key]);
                                }
                                break;
                            case 'SELECT':
                                $tag.val(obj[key]);
                                break;
                            case 'TEXTAREA':
                                $tag.val(obj[key]);
                                break;
                        }
                    }
                }
            }
        },
        /**
         * 函数节流
         * eg--------------------------------
         * throttle(fun, 600, [id, xx, yy], context);
         * function fun(id, xx, yy) { // do some thing }
         * --------------------------------eg
         * // 对频繁调用的函数进行延迟执行，优化性能
         * @param       {Function}      fn              需要节流的函数 [必要]
         * @param       {Number}        delay           延迟时间（单位毫秒，默认不延迟）[可选]
         * @param       {Array}         args            fn接受的参数，是一个数组 [可选]
         * @param       {Object}        context         当前函数执行的作用域 [可选]
         */
        throttle: function (fn, delay, args, context) {
            if (typeof fn !== 'function') {
                return console.error('传入参数有误!')
            }
            // 先清除执行函数定时器句柄
            fn.__throttleID && clearTimeout(fn.__throttleID);
            // 为函数绑定定时器句柄，延迟执行函数
            fn.__throttleID = setTimeout(function () {
                fn.apply(context || null, args || [])
            }, parseInt(delay) || 0);
        },
   
        /**
         * 转换证件为文字
         * @param       {String}        id      证件id
         * @return      {String}                身份证 | 营业执照 | 港澳通行证 | 护照 | 台胞回乡证 | 其它
         * 
         */
        parseIdType: function (id) {
            switch (String(id)) {
                case '1':
                    return '身份证';
                case '2':
                    return '营业执照';
                case '3':
                    return '港澳通行证';
                case '4':
                    return '护照';
                case '5':
                    return '台胞回乡证';
                case '6':
                    return'其它';
            }
        },
        /**
         * 转换客户类型为文字
         * @param       {String}        id      客户类型id
         * @return      {String}                个人 | 机构
         * 
         */
        parseClientType: function (id) {
            switch (String(id)) {
                case '1':
                    return'个人';
                case '2':
                    return'机构';
            }
        },
        /**
         * 转换产品状态为文字
         * @param       {String}        id      产品状态id
         * @return      {String}                募集中 | 存续 | 退出
         * 
         */
        parseProductStatus: function (id) {
            switch (String(id)) {
                case '1':
                    return '募集中';
                case '2':
                    return '存续';
                case '3':
                    return '退出';
            }
        },
        /**
         * 导入文件数据
         * @param       {String}            prefix          实例别名
         * @param       {String}            url             上传url
         * @param       {Object}            pop             pop实例   
         * @param       {Object}            filter          过滤条件        {ext: "xlsx,xls", size: '1024kb'},
         * @param       {String}            tplUrl          模板下载地址      '/dl/import_investment.xlsx',
         * @param       {String}            tplName         定制的模板名称    '导入投资模板.xlsx',
         * @param       {Function}          callback        导入成功的回调         scope.xxxtBST.refresh();
         */
        importXlsxFile: function (param) {
            var gtIE9, IE;
            try {
                gtIE9 = window.navigator.appVersion.split(';')[1].trim().split(' ')[1] > 9;
                IE = /MSIE/.test(window.navigator.appVersion);
            } catch(err) {
                console.error(err);
            }
            if (!scope.uploader) {
                scope.uploader = [];
            } else if (scope.uploader.indexOf(param.prefix) !== -1) {
                return;
            }
            param.tplUrl && param.pop.$form.find('.j-template-url').attr('href', param.tplUrl).attr('download', param.tplName);
            // 初始化上传插件
            require(['plupload', 'CreatePop'], function (plupload, CreatePop) {
                var uploader = new plupload.Uploader({
                    // runtimes: 'html5,flash,silverlight,html4',
                    browse_button: param.pop.$form.find('a.upload-file')[0],
                    url: param.url,
                    flash_swf_url: '/home/script/lib/plupload/Moxie.swf',
                    silverlight_xap_url: '/home/script/lib/plupload/Moxie.xap',
                    container: param.pop.$form[0],
                    multi_selection: false,
                    // multipart_params: {  },
                    filters: {
                        mime_types: [ //限制文件类型
                            { title: "excel files", extensions: param.filter.ext },
                        ],
                        max_file_size: IE && gtIE9 || !IE ? param.filter.size : 0, // 限制文件大小
                    },
                    init: {
                        PostInit: function () {
                            param.pop.$form.find('.j-upload-filename').innerHTML = '';
                            param.pop.sure(function () {
                                uploader.start();
                                return false;
                            });
                        },
                        FilesAdded: function (up, files) {
                            if (uploader.files.length > 1) {
                                uploader.splice(0, 1);
                            }
                            var file = uploader.files[0];
                            param.pop.$form.find('.j-upload-filename').html(file.name + ' (' + plupload.formatSize(file.size) + ')').attr('title', file.name)
                                .next().on('click', function () {
                                    uploader.splice(0, 1);
                                    $(this).prev().html('').removeAttr('title');
                                });;
                        },
                        UploadProgress: function (up, file) {
                            param.pop.$form.find('.j-upload-filename').attr('data-per', file.percent + '%')
                        },
                        // response
                        FileUploaded: function (uploader, file, resObj) {
                            // console.log(uploader, file, resObj)
                            // 处理返回状态码
                            if (resObj.status === 200) { // 
                                var res = JSON.parse(resObj.response);
                                if (res.code === 200) {
                                    param.pop.$form.find('.j-upload-filename').removeAttr('data-per').text('');
                                    // 回传文件id
                                    param.callback(res);
                                } else {
                                    CreatePop.warning(res.msg).afterHide(function () { 
                                        if (res.code === 402) {
                                            window.sessionStorage.setItem('_history_hash', window.location.hash);
                                            window.location = '/login';
                                        }
                                    });
                                }
                            } else {
                                CreatePop.error('错误码' + resObj.status);
                            }
                        },
                        UploadComplete: function (uploader, files) {
                            // console.log(uploader, files);
                        },
                        Error: function (up, err) {
                            // console.error(up, err);
                            -600 === err.code && CreatePop.error('文件大于'+ param.filter.size);
                            param.pop.$form.find('.j-upload-filename').html('').removeAttr('title');
                        }
                    }
                });
                uploader.init();
                // 标记uploader实例已存在
                scope.uploader.push(param.prefix);
            });
        },
        /**
         * 上传文件
         * @param       {String}            prefix
         * @param       {String}            url
         * @param       {Object|String}     btn
         * @param       {Object|String}     container
         * @param       {Object}            filter     {ext: "xlsx,xls", size: '1024kb'},
         * @param       {String}            fileNamePlace ''
         * @param       {String}            fileNameInit ''
         * @param       {Array}             previewSelector ''
         * @param       {Function}          callback
         * 
         */
        uploadFile: function(param) {
            require(['plupload', 'CreatePop'], function (plupload, CreatePop) {
                var uploader = new plupload.Uploader({
                    runtimes : 'html5,flash,silverlight,html4',
                    browse_button : param.btn, // you can pass an id...
                    container: document.getElementById(param.container), // ... or DOM Element itself
                    url : param.url,
                    flash_swf_url : '../lib/plupload/Moxie.swf',
                    silverlight_xap_url : '../lib/plupload/Moxie.xap',
                    filters : {
                        max_file_size : param.filter.size,
                        mime_types: [
                            {title : "upload files", extensions : param.filter.ext }
                        ]
                    },
                    init: {
                        PostInit: function() {
                            document.getElementById(param.fileNamePlace).innerHTML = param.fileNameInit || '';
                            document.getElementById(param.fileNamePlace).removeAttribute('data-per');
                                return false;
                        },
                        FilesAdded: function(up, files) {
                            var isIE = navigator.userAgent.match(/MSIE 9\.0/) != null;
                            plupload.each(files, function(file) {
                                document.getElementById(param.fileNamePlace).innerHTML =  file.name + ' (' + plupload.formatSize(file.size) + ')';
                            });
                            uploader.start();
                        },
                        UploadProgress: function(up, file) {
                            document.getElementById(param.fileNamePlace).setAttribute('data-per', file.percent + '%');
                        },
                        FileUploaded: function (uploader, file, resObj) {
                            // console.log(uploader, file, resObj)
                            // 处理返回状态码
                            if (resObj.status === 200) { // 
                                var res = JSON.parse(resObj.response);
                                if (res.code === 200) {
                                    // document.getElementById(param.fileNamePlace).innerHTML = '(推荐尺寸：900*500,图片大小不超过64K)';
                                    document.getElementById(param.fileNamePlace).removeAttribute('data-per');
                                    param.previewSelector && previewImage(file, function(src) {
                                        param.previewSelector[0] && $(param.previewSelector[0]).attr('src', src).removeClass('hide');
                                        param.previewSelector[1] && $(param.previewSelector[1]).attr('src', src).attr('data-src', src);
                                    });
                                    param.callback(res);
                                } else {
                                    CreatePop.warning(res.msg).afterHide(function () { 
                                        if (res.code === 402) {
                                            window.sessionStorage.setItem('_history_hash', window.location.hash);
                                            window.location = '/login';
                                        }
                                    });
                                }
                            } else {
                                CreatePop.error('错误码' + resObj.status);
                            }
                        },
                        Error: function(up, err) {
                            console.log(up, err);
                            CreatePop.warning(err.message);
                            // document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
                        }
                    }
                });
                uploader.init();
                // 图片预览
                function previewImage(file, callback) { //file为plupload事件监听函数参数中的file对象,callback为预览图片准备完成的回调函数
                    if (!file || !/image\//.test(file.type)) return;
                    if (file.type == 'image/gif') { //gif使用FileReader进行预览,因为mOxie.Image只支持jpg和png
                        var fr = new plupload.moxie.file.FileReader();
                        fr.onload = function() {
                            callback(fr.result);
                            fr.destroy();
                            fr = null;
                        }
                        fr.readAsDataURL(file.getSource());
                    } else {
                        var preloader = new plupload.moxie.image.Image();
                        preloader.onload = function() {
                            preloader.downsize(300, 300); //先压缩一下要预览的图片,宽300，高300
                            var imgsrc = preloader.type == 'image/jpeg' ? preloader.getAsDataURL('image/jpeg', 80) : preloader.getAsDataURL(); //得到图片src,实质为一个base64编码的数据
                            callback && callback(imgsrc); //callback传入的参数为预览图片的url
                            preloader.destroy();
                            preloader = null;
                        };
                        preloader.load(file.getSource());
                    }
                }
            });
        },
        /**
         * 获取评论
         * @param       {String}        url         请求地址 /xx/comments
         * @param       {String}        method      默认请求方法POST
         * @param       {Object}        param       请求参数    { offset: 0, limit: 10 }
         * @param       {Object}        $tip        提示的dom（jq DOM）
         * @param       {Object}        $scrollContent    滚动体
         * @param       {Function}      success     成功回调函数    fun(res) { xxx }
         * @param       {Function}      error       失败回调函数    fun(err) { xxx }
         * 
         */ 
        getComments: function(opt) {
            // 滚动
            opt.$scrollContent.parent().on('scroll', function () {
                clearTimeout(opt.timerHandle);
                opt.timerHandle = setTimeout(function () {
                    if (opt.param.over) { return; }
                    var viewHeight = opt.$scrollContent.parent().height(), // 当前滚动区域可视高度
                        scrollHeight = opt.$scrollContent.height(), // 当前滚动体高度
                        scrollTop = opt.$scrollContent.offset().top;  // 当前滚动体卷去的高度
                    if (scrollHeight < viewHeight - scrollTop + 50) { // 到底部了
                        loadMore(opt);
                    }
                }, 300);
            });
            // 加载评论
            function loadMore(opt) {
                if (opt.over) { return; }
                opt.$tip.text('正在加载更多评论');
                $[opt.method || 'post'](opt.url, opt.param).done(function (res) {
                    if (res.code === 200) {
                        if (!res.data.list.length && !opt.param.offset) {
                            opt.$tip.text('暂无评论');
                            opt.param.over = true;
                            return;
                        } else if (res.data.list.length < opt.param.limit) {
                            opt.$tip.text('全部加载完毕');
                            opt.param.over = true;
                        } else {
                            opt.$tip.text('');
                        } 
                        opt.param.offset = opt.param.offset + opt.param.limit;
                        opt.success(res.data.list);
                    } else {
                        opt.error(res.msg);
                    }
                });
            }
            loadMore(opt);
        },
        /**
         * @desc    获取评论回复
         * @param       {String}            url         请求地址
         * @param       {String}            method      默认post
         * @param       {String|Object}     commentList jq selector
         * @param       {String}            delegate    点击加载的按钮的类 带data-id属性，附带id参数
         * @param       {Number}            limit       限制加载条数    
         */ 
        getReply: function(opt) {
            opt.commentsList.on('click', opt.delegate, function () {
                this.offsetItems || (this.offsetItems = 0);
                var offset = this.offsetItems,
                    that = this;
                    !this.ItemsOver && $[open.method || 'post'](opt.url, { id: $(this).data('id'), limit: opt.limit, offset: offset || 0 })
                    .done(function(res) {
                        if (res.code === 200) {
                            utils.repeatDOM(res.data.list, 'reply', $(that).prev(), true);
                            that.offsetItems += opt.limit;
                            if (res.data.list.length < opt.limit) {
                                that.ItemsOver = true;
                                $(that).text('已加载全部回复').addClass('txt-grey').removeClass('j-get-reply-btn link');
                            } else {
                                $(that).text('加载更多回复↓');
                            }
                        }
                });
            });
        },
        /**
         * 获取已读成员
         * @param       {String}        url         请求url
         * @param       {String}        method      (默认POST)
         * @param       {String}        param       请求参数
         * @param       {String}        popBlast    触发弹窗的按钮（jq selector）
         */
        getHadReadMember: function(opt) {
            require(['CreatePop', 'utils'], function(CreatePop, utils) {
                new CreatePop({
                    type: 'prompt',
                    name: 'hadRead',
                    title: '已读(0)',
                    blast: opt.popBlast,
                    autoHide: false,
                    beforeShowCallBack: function () {
                        // 获取已读名单
                        var that = this;
                       $[opt.method || 'post'](opt.url, opt.param).done(function (res) {
                            if (res.code === 200) {
                                // 设置标题
                                that.setTitle('已读(' + res.data.member.length + ')');
                                // 删除之前的DOM
                                $(that.popID).find('[repeat-dom]').siblings().remove();
                                if (res.data.member.length) {
                                    utils.repeatDOM(res.data.member, 'hadRead', that.popID, true); // 生成DOM
                                    $(that.popID).find('.tip').text('');
                                } else {
                                    $(that.popID).find('.tip').text('无已读成员');
                                }
                            }
                        });
                    },
                });
            });
        },
        /**
         * @desc        获取未读成员
         * @param       {String}        url         请求url
         * @param       {String}        remindUrl   一键提醒url
         * @param       {String}        method      (默认POST)
         * @param       {String}        param       请求参数
         * @param       {String}        popBlast    触发弹窗的按钮（jq selector）
         */
        getNotReadMember: function(opt) {
            require(['CreatePop', 'utils'], function(CreatePop, utils) {
                new CreatePop({
                    type: 'prompt',
                    name: 'hadNotRead',
                    title: '未读(0)',
                    blast: opt.popBlast,
                    autoHide: false,
                    beforeShowCallBack: function () {
                        // 获取未读名单
                        var that = this;
                        $[opt.method || 'post'](opt.url, opt.param).done(function (res) {
                            if (res.code === 200) {
                                // 设置标题
                                that.setTitle('未读(' + res.data.member.length + ')');
                                // 删除之前的DOM
                                $(that.popID).find('[repeat-dom]').siblings().remove();
                                // 生成DOM
                                if (res.data.member.length) {
                                    utils.repeatDOM(res.data.member, 'notRead', that.popID, true); 
                                    $(that.popID).find('.tip').text('');
                                } else {
                                    $(that.popID).find('.tip').text('无未读成员');
                                }
                                // 是否可以提醒
                                opt.can_remind = Number(res.data.can_remind);
                                if (opt.can_remind) {
                                    $(that.popID).find('button').removeAttr('disabled')[0].className = 'btn-theme';
                                } else {
                                    $(that.popID).find('button').attr('disabled', true)[0].className = 'btn-cancel';
                                }
                                scope.remindRead = opt;
                            }
                        });
                    },
                    sureCallBack: function () {
                        if (scope.remindRead.can_remind) {
                            this.hide();
                            // 未读一键提醒
                            $[scope.remindRead.method || 'post'](scope.remindRead.remindUrl, scope.remindRead.param).done(function (res) {
                                 if (res.code === 200) {
                                     CreatePop.tip('提醒成功');
                                 }
                             });
                        }
                    }
                });
            });
        },
        /**
         * @desc    百度编辑器
         * @param   {Object}    UE          UE构造函数
         * @param   {String}    id          dom id
         * @param   {Object}    option      ueditor option
         * @param   {Boolean}   valid       是否校验ue是否有值
         * @return  {Object}    返回 ueditor 实例
         * 
         */ 
        initUE: function(UE, id, option, valid) {
            var option = $.extend({
                autoHeightEnabled: false,
                initialFrameWidth: 960,
                initialFrameHeight: 200,
                maximumWords: 10000000,
            }, option);
            var editor = new UE.ui.Editor(option);
            editor.render(id);
            // 校验ue是否有值
            valid && editor.addListener('contentChange', function () {
                editor.getContent() 
                ? $('.edui-editor').removeClass('has-error').parents('.editor-wrapper').next().hide() 
                : $('.edui-editor').addClass('has-error').parents('.editor-wrapper').next().text('这是必填字段').show();
            });
            return editor;
        }
    };
    return utils;
});