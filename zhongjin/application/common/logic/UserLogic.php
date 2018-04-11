<?php
/**
 * 用户操作逻辑层
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\User;
use app\common\controller\Json;
use app\common\controller\Errcode;

class UserLogic{
	
	/**
	 * 增加管理员
	 * @param string $account		账号
	 * @param string $password		密码
	 * @param string $mobile		手机号
	 * @param string $name			姓名
	 */
	public static function add($account, $password, $mobile, $name)
	{
		$model = new User();
		
		$info = $model->getOne(['mobile'=>$mobile, 'is_del'=>0], 'id');
		if(! empty($info)) return -1;
		
		$id = $model->addOne([
			'account'	=> $account,
			'password'	=> $password,
			'mobile'	=> $mobile,
			'name'		=> $name
		]);
		
		return $id > 0 ? $id : false;
	}
	
	/**
	 * 登录
	 * @param string $username	账号
	 * @param string $pwd		密码
	 */
	public static function login($username, $pwd)
	{
		$model = new User();
		$find = $model->getOne(['account'=>$username, 'password'=>$pwd, 'is_del'=>0], 'id');
		return empty($find) ? false : $find['id'];
	}
	
	/**
	 * 修改密码
	 * @param int $id		账号的记录ID
	 * @param string $pwd	密码
	 */
	public static function modify($id, $pwd)
	{
		$model = new User();
		$res = $model->updateMore(['id'=>$id], ['password'=>$pwd]);
		return $res >0 ? true : false;
	}
	
	/**
	 * 获取验证码
	 * @param string $username	账号
	 */
	public static function getPhoneCode($username)
	{
		$model = new User();
		$find = $model->getOne(['account'=>$username], 'mobile,id');
		empty($find) && Json::result('', Errcode::USER_UNSET, '用户名不存在');
		
		$code = CodeLogic::getCode($find['mobile'], 1800);
		$content = "【中金】您在中金平台找回密码的短信验证码为{$code},有效时间为30分钟,请及时处理!";
		$res = CodeLogic::sendMsg($find['mobile'], $content, $find['id']);
		if(! $res){
			// 发送短信失败
			CodeLogic::delCode($code);
			return false;
		}
		return true;
	}
	
	/**
	 * 密码找回 即修改密码
	 * @param string $username		账号
	 * @param string $pwd			密码
	 * @param string $phoneCode		手机验证码
	 */
	public static function pwdFind($username, $pwd, $phoneCode)
	{
		$model = new User();
		$find = $model->getOne(['account'=>$username, 'is_del'=>0], 'mobile,id,password');
		empty($find) && Json::result('', Errcode::USER_UNSET, '用户名不存在');
		if($find['password'] == $pwd ) return Errcode::OLD_NEW_EQUALLY;
		
		// 验证码校验
		$res = CodeLogic::verifyCode($find['mobile'], $phoneCode);
		if(! $res){
			// 验证错误
			return Errcode::PHONR_CODE_ERR;
		}
		
		$res = $model->updateMore(['id'=>$find['id']], ['password'=>$pwd]);
		return $res <=0 ? Errcode::UPDATE_ERR : Errcode::SUCCESS;
	}

	/**
	 * 根据手机号码获取信息
	 * @param string $mobile	手机号
	 * @param string $field		字段
	 */
	public static function getByPhone($mobile, $field='*')
	{
		$model = new User();
		$find = $model->getOne(['mobile'=>$mobile], 'id');
		
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 获取ids中不存在管理员的ids 并返回存在的信息
	 * @param array $ids		管理员ids
	 */
	public static function getUnset($ids)
	{
		$model = new User();
		
		$info = [];
		$_info = $model->getMore(['id'=>['in', $ids], 'is_del'=>0], 'id,name,mobile');
		foreach($_info as $v){
			$info[$v['id']] = $v;
		}
		unset($_info);
		
		return [$info, array_values(array_diff($ids, array_keys($info)))];
	}
	
	/**
	 * 根据ids获取信息
	 * @param array $ids	管理员ids
	 */
	public static function getNameByUserids($ids)
	{
		$model = new User();
		
		$info = [];
		$_info = $model->getMore(['id'=>['in', $ids], 'is_del'=>0], 'id,name');
		foreach($_info as $v){
			$info[$v['id']] = $v['name'];
		}
		unset($_info);
		
		return $info;
	}
	
}
