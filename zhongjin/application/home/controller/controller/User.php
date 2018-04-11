<?php
namespace app\home\controller;

use think\Controller;
use think\captcha\Captcha;
use app\common\controller\Errcode;
use app\common\logic\UserLogic;

class User extends Controller
{
	/**
	 * 登录
	 */
    public function login()
    {
		$code = input('imgcode', '', 'trim');
		$username = input('username', '', 'trim');
		$pwd = input('password', '', 'md5');
		empty($username) && $this->result('', Errcode::PARAM_ERR, '请输入用户名');
		empty($code) && $this->result('', Errcode::PARAM_ERR, '请输入验证码');
		empty($pwd) && $this->result('', Errcode::PARAM_ERR, '请输入密码');
		
		$captcha = new Captcha();
		$captcha->check($code) || $this->result('', Errcode::CAPTCHA_FAILE, '验证码错误');
		
		$id = UserLogic::login($username, $pwd);
		$id == false && $this->result('', Errcode::USER_PWD_ERR, '用户名或密码错误');
		session('user',$id);
		return ['code'=>Errcode::SUCCESS];
    }
	
	/**
	 * 是否已经登录
	 */
	public function isLogin()
	{
		$id = session('user');
		if((empty($id) || $id <=0)){
			return ['code'=>Errcode::LOGIN_INVALID];
		}
		
		return ['code'=>Errcode::SUCCESS];
	}
	
	/**
	 * 生成验证码
	 */
	public function vcode()
	{
		$id = input('param.id', '', 'trim');
		$captcha = new Captcha(['length'=>4,'useNoise'=>false]);
		return $captcha->entry($id);
	}
	
	/**
	 * 修改密码
	 */
	public function modify()
	{
		$loginid = session('user');
		($loginid == null || $loginid <=0) && $this->result('', Errcode::LOGIN_INVALID, '请先登录');
		$oldPwd = input('old_pass', '', 'md5');
		$newPwd = input('new_pass', '', 'md5');
		$confirmPwd = input('confirm_pass', '', 'md5');
		
		strcmp($newPwd, $confirmPwd) && $this->result('', Errcode::PWD_CONFIRM_ERR, '输入的新密码不一致');
		strcmp($oldPwd, $newPwd) || $this->result('', Errcode::OLD_NEW_EQUALLY, '新密码不能与原密码一致');
		
		$res = UserLogic::modify($loginid, $newPwd);
		!$res && $this->result('', Errcode::UPDATE_ERR, '修改失败');
		
		session(null);
		return ['code'=>Errcode::SUCCESS];
	}
	
	/**
	 * 忘记密码
	 */
	public function pwdFind()
	{
		$username = input('param.username', '', 'trim');
		$phoneCode = input('param.phonecode', '', 'trim');
		$pwd = input('param.password', '', 'md5');
		$confirmPwd = input('param.confirmpassword', '', 'md5');
		$code = input('param.imgcode', '', 'trim');
		
		(empty($username) || empty($phoneCode) || empty($code)) &&
			$this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$captcha = new Captcha();
		$captcha->check($code) || $this->result('', Errcode::CAPTCHA_FAILE, '验证码错误');
		
		$res = UserLogic::pwdFind($username, $pwd, $phoneCode);
		isset($res['code']) && $this->result('', Errcode::PHONR_CODE_ERR, '手机验证码错误');
		!$res && $this->result('', Errcode::INSERT_ERR, '更新密码失败');
		
		return ['code'=>Errcode::SUCCESS];
	}
	
	/**
	 * 注销
	 */
	public function logout()
	{
		session('user', null);
		return ['code'=>Errcode::SUCCESS];
	}
	
	/**
	 * 获取手机验证码
	 */
	public function getPhoneCode()
	{
		$username = input('param.username', '', 'trim');
		empty($username) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = UserLogic::getPhoneCode($username);
		!$res && $this->result('', Errcode::PHONR_CODE_ERR, '发送验证码失败');
		
		return ['code'=>Errcode::SUCCESS];
	}
}
