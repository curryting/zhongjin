<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    /*'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => 'pc_',
        // 缓存有效期 0表示永久缓存
        'expire' => 0
    ],*/

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'				=> '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id'	=> '',
        // SESSION 前缀
        'prefix'			=> 'pc_',
        // 驱动方式 支持redis memcache memcached
        'type'				=> 'File',
        // 是否自动开启 SESSION
        'auto_start'		=> true,
		// 有效期(秒)
		'expire'			=> 3600*2,
		'use_cookies'		=> 1,
		'domain'			=> WEB_DOMAIN,
		'path'				=> SESSION_PATH
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    /*'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],*/
];
