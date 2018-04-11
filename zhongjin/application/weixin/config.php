<?php
return [
	// +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'				=> '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id'	=> '',
        // SESSION 前缀
        'prefix'			=> 'wx_',
        // 驱动方式 支持redis memcache memcached
        'type'				=> 'File',
        // 是否自动开启 SESSION
        'auto_start'		=> true,
		// 有效期(秒)
		'expire'			=> 60 * 60,
		'use_cookies'		=> 1,
		'domain'			=> WEB_DOMAIN,
		'path'				=> SESSION_PATH
    ]
];
