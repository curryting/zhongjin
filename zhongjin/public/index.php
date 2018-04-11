<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

error_reporting ( E_ALL );
if(version_compare(PHP_VERSION, '5.3.0', '<'))	die('Your PHP Version is ' . PHP_VERSION . ', But WeiPHP require PHP > 5.3.0 !');

/**
 * 系统环境
 * 0-生产环境；1-测试环境；2-本地调试
 */
define('APP_LOCAL', 1);

// 网站站点ID,使用省市ID,详见province_city_area表
define('SITE_ID', 0);

// 站点HTTP协议
define('APP_HTTP', 'http://');

// 一级域名 top-level domain
define('TLD', 'zj');

// 网站二级主域名
define('WEB_DOMAIN', (APP_LOCAL == 0) ? 'jxhl.com' : 'weiwojiaoyu.com');

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 缓存目录设置
define('RUNTIME_PATH', dirname(realpath(APP_PATH)).DIRECTORY_SEPARATOR.'../zhongjin_runtime/');

// 上传目录设置
define('UPLOADS_PATH', __DIR__.'/uploads/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
