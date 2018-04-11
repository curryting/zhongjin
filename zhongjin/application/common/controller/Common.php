<?php
namespace app\common\controller;

use think\Controller;

class Common extends Controller{
	
	protected function _initialize() 
	{
		if(APP_LOCAL == 1){
			// 测试环境开放
			header("Access-Control-Allow-Origin:*"); //允许哪些url可以跨域请求到本域
			header("Access-Control-Allow-Methods:POST,GET"); //允许的请求方法，一般是GET,POST,PUT,DELETE,OPTIONS
			header("Access-Control-Allow-Headers:x-requested-with,content-type"); //允许哪些请求头可以跨域
			header('Access-Control-Allow-Credentials: true');
		}
	}
	
	/**
	 * pc端访问权限
	 */
	protected function authPc()
	{
		$loginFlag = session('user');
		($loginFlag <=0 || empty($loginFlag)) && $this->result('', Errcode::LOGIN_INVALID, '登录失效,请重新登录');
		if(! defined('ADMINID'))		define('ADMINID', $loginFlag);  // 不是管理员，此常量则为0
	}

	/**
	 *  移动端访问权限
	 */
	protected function authWx()
	{
        // 应用ID
        $aid = input('param.aid', -1, 'intval');
        if($aid >= 0)   session('agentid', $aid);
		
		$memid = input('param.memid', -1, 'intval');
		if($memid <0){
			$memid = intval(session('customer'));
		}

		$adminid = input('param.adminid', -1, 'intval');
		if($adminid <0){
			$adminid = intval(session('admin'));
		}
		
		(empty($memid) && empty($adminid)) && $this->result('', Errcode::LOGIN_INVALID, '登录失效');
		if(! defined('MEMID'))		define('MEMID', $memid);		// 管理员身份，此常量则为0
		if(! defined('ADMINID'))		define('ADMINID', $adminid);  // 不是管理员，此常量则为0
	}
	
}

