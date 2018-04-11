define(['Router', 'jquery', 'DAO'], function (Router, $, DAO) {
    'use strict';


    /**
     *  html模板路径
     */
    var basePath = '/home/view';
    var tplPath = {
        // 首页
        index: basePath + '/home/home.html',
        // 公司
        company: {
            profile: basePath + '/company/profile.html'
        },
        // 客户
        client: {
            list: basePath + '/client/list.html',
            detail: basePath + '/client/detail.html',
            report: basePath + '/client/report.html'
        },
        // 产品
        product: {
            add: basePath + '/product/add.html',
            list: basePath + '/product/list.html',
            edit: basePath + '/product/edit.html',
            detail: basePath + '/product/detail.html'
        },
        // 新闻
        news: {
            add: basePath + '/news/add.html',
            list: basePath + '/news/list.html',
            detail: basePath + '/news/detail.html'
        },
        // 通知
        notice: {
            add: basePath + '/notice/add.html',
            list: basePath + '/notice/list.html',
            edit: basePath + '/notice/edit.html',
            detail: basePath + '/notice/detail.html'
        },
        // 投票
        vote: {
            add: basePath + '/vote/add.html',
            option: basePath + '/vote/option.html',
            setting: basePath + '/vote/setting.html',
            list: basePath + '/vote/list.html',
            statistics: basePath + '/vote/statistics.html',
        },
        // 调研
        survey: {
            add: basePath + '/survey/add.html',
            option: basePath + '/survey/option.html',
            setting: basePath + '/survey/setting.html',
            list: basePath + '/survey/list.html',
            statistics: basePath + '/survey/statistics.html',
            paper: {
                list: basePath + '/survey/paper/list.html',
                detail: basePath + '/survey/paper/detail.html',
            }
        },
        // 投资
        invest: {
            add: basePath + '/invest/add.html',
            list: basePath + '/invest/list.html',
            detail: basePath + '/invest/detail.html'
        },
        // 应用设置
        setting: {
            option: basePath + '/setting/option.html',
            menu: basePath + '/setting/menu.html',
        },
    };


    /**
     *  路由配置表
     */
    var routerTable = {
        // 首页
        index: {
            // 主页
            home: {
                path: '#/index',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.index,
                    jsPath: ['index'],
                    pageFn: 'home',
                    pageTitle: '首页',
                    callback: null
                }
            }
        },
        // 公司
        company: {
            // 公司简介
            profile: {
                path: '#/profile',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.company.profile,
                    jsPath: ['company'],
                    pageFn: 'profile',
                    pageTitle: '公司简介',
                    callback: null
                }
            }
        },
        // 客户管理
        client: {
            // 添加客户
            list: {
                path: '#/client/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.client.list,
                    jsPath: ['client'],
                    pageFn: 'list',
                    pageTitle: '添加客户',
                    callback: null
                }
            },
            // 客户列表
            detail: {
                path: '#/client/detail/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.client.detail,
                    jsPath: ['client'],
                    pageFn: 'detail',
                    pageTitle: '客户详情',
                    callback: null
                }
            },
            // 投资报告列表
            report: {
                path: '#/client/invest/report/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.client.report,
                    jsPath: ['client'],
                    pageFn: 'reportList',
                    pageTitle: '投资报告',
                    callback: null
                }
            }
        },
        // 产品管理
        product: {
            add: {
                path: '#/product/add',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.product.add,
                    jsPath: ['product'],
                    pageFn: 'add',
                    pageTitle: '新增产品',
                    callback: null
                }
            },
            list: {
                path: '#/product/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.product.list,
                    jsPath: ['product'],
                    pageFn: 'list',
                    pageTitle: '产品列表',
                    callback: null
                }
            },
            detail: {
                path: '#/product/detail/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.product.detail,
                    jsPath: ['product'],
                    pageFn: 'detail',
                    pageTitle: '产品详情',
                    callback: null
                }
            },
            edit: {
                path: '#/product/edit/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.product.edit,
                    jsPath: ['product'],
                    pageFn: 'edit',
                    pageTitle: '修改产品',
                    callback: null
                }
            }
        },
        // 新闻
        news: {
            add: {
                path: '#/news/add',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.news.add,
                    jsPath: ['news'],
                    pageFn: 'add',
                    pageTitle: '新建新闻',
                    callback: null
                }
            },
            list: {
                path: '#/news/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.news.list,
                    jsPath: ['news'],
                    pageFn: 'list',
                    pageTitle: '新闻列表',
                    callback: null
                }
            },

            detail: {
                path: '#/news/detail/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.news.detail,
                    jsPath: ['news'],
                    pageFn: 'detail',
                    pageTitle: '新闻列表',
                    callback: null
                }
            },

        },
        // 通知
        notice: {
            add: {
                path: '#/notice/add',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.notice.add,
                    jsPath: ['notice'],
                    pageFn: 'add',
                    pageTitle: '新建通知',
                    callback: null
                }
            },
            list: {
                path: '#/notice/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.notice.list,
                    jsPath: ['notice'],
                    pageFn: 'list',
                    pageTitle: '通知列表',
                    callback: null
                }
            },
            detail: {
                path: '#/notice/detail/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.notice.detail,
                    jsPath: ['notice'],
                    pageFn: 'detail',
                    pageTitle: '通知详情',
                    callback: null
                }
            }
        },
        // 投票
        vote: {
            add: {
                path: '#/vote/add/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.vote.add,
                    jsPath: ['vote'],
                    pageFn: 'add',
                    pageTitle: '投票内容',
                    callback: null
                }
            },
            option: {
                path: '#/vote/option/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.vote.option,
                    jsPath: ['vote'],
                    pageFn: 'option',
                    pageTitle: '投票选项',
                    callback: null
                }
            },
            setting: {
                path: '#/vote/setting/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.vote.setting,
                    jsPath: ['vote'],
                    pageFn: 'setting',
                    pageTitle: '投票设置',
                    callback: null
                }
            },
            list: {
                path: '#/vote/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.vote.list,
                    jsPath: ['vote'],
                    pageFn: 'list',
                    pageTitle: '投票列表',
                    callback: null
                }
            },
            statistic: {
                path: '#/vote/statistics/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.vote.statistics,
                    jsPath: ['vote'],
                    pageFn: 'statistics',
                    pageTitle: '投票统计',
                    callback: null
                }
            }

        },
        // 调研
        survey: {
            add: {
                path: '#/survey/add/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.add,
                    jsPath: ['survey'],
                    pageFn: 'add',
                    pageTitle: '新建调研',
                    callback: null
                }
            },
            option: {
                path: '#/survey/option/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.option,
                    jsPath: ['survey'],
                    pageFn: 'option',
                    pageTitle: '调研问题',
                    callback: null
                }
            },
            list: {
                path: '#/survey/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.list,
                    jsPath: ['survey'],
                    pageFn: 'list',
                    pageTitle: '调研列表',
                    callback: null
                }
            },
            statistics: {
                path: '#/survey/statistics/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.statistics,
                    jsPath: ['survey'],
                    pageFn: 'statistics',
                    pageTitle: '调研统计',
                    callback: null
                }
            },
            paperList: {
                path: '#/survey/paper/list/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.paper.list,
                    jsPath: ['survey'],
                    pageFn: 'paperList',
                    pageTitle: '答案列表',
                    callback: null
                }
            },
            paperDetail: {
                path: '#/survey/paper/detail/:sid/:pid/:mid', // surveyId, memberId, paperId
                on: routeCallBack,
                config: {
                    templatePath: tplPath.survey.paper.detail,
                    jsPath: ['survey'],
                    pageFn: 'paperDetail',
                    pageTitle: '答卷详情',
                    callback: null
                }
            }
        },
        // 投资
        invest: {
            add: {
                path: '#/invest/add',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.invest.add,
                    jsPath: ['invest'],
                    pageFn: 'add',
                    pageTitle: '添加投资',
                    callback: null
                }
            },
            list: {
                path: '#/invest/list',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.invest.list,
                    jsPath: ['invest'],
                    pageFn: 'list',
                    pageTitle: '投资列表',
                    callback: null
                }
            },
            detail: {
                path: '#/invest/detail/:id',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.invest.detail,
                    jsPath: ['invest'],
                    pageFn: 'detail',
                    pageTitle: '投资详情',
                    callback: null
                }
            },
        },
        // 应用设置
        setting: {
            menu: {
                path: '#/setting/menu',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.setting.menu,
                    jsPath: ['menu'],
                    pageFn: 'menu',
                    pageTitle: '应用菜单',
                    callback: null
                }
            },
            option: {
                path: '#/setting/option',
                on: routeCallBack,
                config: {
                    templatePath: tplPath.setting.option,
                    jsPath: ['option'],
                    pageFn: 'option',
                    pageTitle: '应用设置',
                    callback: null
                }
            }
        },
    };

    /**
     * 保存路由参数为临时数据
     */
    var dao = new DAO('router');

    /**
     *  初始化路由
     */
    for (var key in routerTable) {
        'path' in routerTable[key] && Router.add(routerTable[key]);
        for (var _key in routerTable[key]) {
            'path' in routerTable[key][_key] && Router.add(routerTable[key][_key]);
        }
    }
    Router.init(onRouteChange, onRouteNotFound);


    /**
     *  初始侧边菜单
     */
    reActiveMenu();
    function reActiveMenu() {
        // 刷新页面， 重新激活测侧边菜单
        // 获取激活的父级菜单id
        var parentMenuId = dao.get('p_m_id').value;
        // 获取激活的子菜单id
        var subMenuId = dao.get('s_m_id').value;
        if (parentMenuId) { // 有父级菜单
            // 激活对应的父级菜单
            $('#' + parentMenuId).addClass('active');
            if (subMenuId) { // 有子菜单
                // 激活对应的子菜单
                $('#' + subMenuId).addClass('current').parents('.treeview-menu').slideDown(300);
            }
        }

        // 绑定菜单点击事件
        $('.treeview-title').on('click', function () {
            $(this).next().stop().slideToggle(300);
            $(this).parent().toggleClass('active')
                .siblings('.treeview').not('.j-no-sub').removeClass('active')
                .find('.treeview-menu').slideUp(300);
        });
    }

    /**
     *  has 变化时，保存菜单激活样式
     */
    function onRouteChange(obj) {
        // 匹配已经激活的菜单
        $('.treeview').find('a').each(function (index, ele) {
            if ($(ele).attr('href') === obj.url) {
                // 判断有无子菜单
                if ($(ele).hasClass('treeview-title')) { // 菜单为 ‘首页’ 或 ‘公司’，无子菜单
                    // 设置父级菜单id， 并激活 
                    dao.set('p_m_id', $(ele).parent().attr('id'));
                    // 移除子菜单id
                    dao.remove('s_m_id');
                    // 激活菜单
                    $(ele).parent().addClass('active')
                        .siblings().removeClass('active')
                        .find('.treeview-menu').slideUp(300)
                        .find('li').removeClass('current');
                } else { // 有子菜单
                    // 设置父级菜单id
                    dao.set('p_m_id', $(ele).parents('.treeview').attr('id'));
                    // 设置子菜单id
                    dao.set('s_m_id', $(ele).parent().attr('id'));
                    // 激活菜单
                    $(ele).parent().addClass('current')
                        .siblings().removeClass('current').end()
                        .parents('.treeview').addClass('active')
                        .find('.treeview-menu').slideDown(300).end()
                        .siblings().removeClass('active')
                        .find('.treeview-menu').slideUp(300)
                        .find('li').removeClass('current');
                }
            }
        });
    }
    /**
     * hash不存在时，回调函数
     */
    function onRouteNotFound(res) {
        // CreatePop.error('路由: <del>' + window.location.host + '/#/' + res.join('/') + '</del> 不存在！');
        Router.navigate('#/index');
    }
    // 路由激活时的回调函数
    function routeCallBack() {
        // this.params, this.path, this.url
        loadPage({
            templatePath: this.config.templatePath,
            jsPath: this.config.jsPath,
            pageFn: this.config.pageFn,
            pageTitle: this.config.pageTitle,
            args: this, // 路由参数
            callback: this.config.callback,
        });
    };

    /**
     * 先载入html片段，再载入js，并执行js中定义好的函数
     * 
     * @param      {string}       templatePath      // html页面的地址
     * @param      {Array}        jsPath            // js requirejs的加载地址    
     * @param      {string}       pageFn            // js 中对应的函数名称
     * @param      {string}       title             // 当前页面的标题
     * @param      {Object}       args              // 要出入js中的参数   
     * @param      {function}     callback          // 页面加载完成后要执行的回调函数
     */
    function loadPage(option) {

        var templatePath = option.templatePath || tplPath.index;
        var jsPath = option.jsPath || null;
        var pageFn = option.pageFn || null;
        var pageTitle = option.pageTitle || '首页';
        var args = option.args || null;
        var callback = option.callback || null;
        // 加载html
        $('#j_load_container').load(templatePath, function () {
            // 加载js
            jsPath && require(jsPath, function (main) {
                // 执行对应函数并把参数传入
                pageFn && main[pageFn](args);
                // 设置页面标题
                document.title = pageTitle + ' -- 鼎心资本'
                // 执行回调
                callback && callback(args);
            });
            !jsPath && console.error('未配置js文件路径');
            !pageFn && console.error('未配置js文件执行函数');
            !pageTitle && console.error('未配置页面标题');
            !args && console.error('未配置路由参数');
        });
    }


});
