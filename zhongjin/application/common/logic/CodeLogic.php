<?php
/**
 * 手机验证码 以及发短信
 * by sherlock
 */

namespace app\common\logic;
use app\common\model\Code;
use app\common\model\Estone;
use app\common\controller\Json;
use app\common\controller\Errcode;

class CodeLogic{
	/**
	 * 获取手机验证码
	 * @param string $mobile 手机号码
	 * @param string $expire 有效时间
	 */
	public static function getCode($mobile, $expire)
	{
		$expire = intval($expire);
		$mobile = trim($mobile);
    
		$expireTime = NOW_TIME-86400;  // 控制一天只能发6条
		$model = new Code();
		$list = $model->getMore([
			'mobile'=>$mobile, 'create_time'=>['gt',$expireTime]
		], 'create_time,code', 'id DESC');
		
		count($list) > 6 && Json::result('', Errcode::PHONR_CODE_ERR, '24小时内只能获取六条验证码');
		
		$latest = reset($list);
		(isset($latest['create_time']) && intval($latest['create_time']) > (NOW_TIME-120)) &&
		Json::result('', Errcode::PHONR_CODE_ERR, '二分钟内只能获取一条验证码');
		
		$code = get_random_code($mobile, 6);
		$id = $model->addOne([
			'code'		=> $code,
			'expire'	=> NOW_TIME + $expire,
			'mobile'	=> $mobile,
			'create_time'	=> NOW_TIME
		]);
		$id <=0 && Json::result('', Errcode::INSERT_ERR, '获取验证码失败');
		
		return $code;
	}
	
	/**
	 * 删除最新的一条数据
	 * @param string $code	验证码
	 */
	public static function delCode($code)
	{
		$model = new Code();
		$find = $model->getOne(['code'=>$code], 'id', 'id DESC');
		
		if($find){
			$res = $model->del($find['id']);
		}
		
		return (isset($res) && $res>0) ? true : false;
	}
	
	/**
	 * 验证手机验证码是否正确
	 * @param string $mobile 手机号码
	 * @param string $code	验证码
	 */
	public static function verifyCode($mobile, $code)
	{
		$model = new Code();
		$find = $model->getOne(['mobile'=>$mobile, 'code'=>$code], 
			'id,expire', 'id DESC');
		if(! empty($find)){
			intval($find['expire']) < NOW_TIME	&&
				Json::result('', Errcode::PHONR_CODE_INVALID, '验证码已失效');
			
			// 更新状态
			$model->updateMore(['id'=>$find['id']], ['status'=>1]);
			return true;
		}
		
		return false;
	}
	
	/**
	 * 发送短信
	 * @param string $mobile	手机号码
	 * @param string $content	待发送的内容
	 * @param int	$id			账号ID
	 */
	public static function sendMsg($mobile, $content, $id)
	{
		empty($content) && Json::result('', Errcode::PARAM_ERR, '发送内容不能为空');
		is_mobile($mobile) || Json::result('', Errcode::PARAM_ERR, '手机号码格式错误');
		
		// 壹通道提交网关每次字数限制300
		$content_split = str_split_unicode($content, $length, 300);
		
		$model = new Estone();
		$strdate = date('Y-m-d H:i:s');
		$id = $model->addOne([
			'CREATE_TIME'=>$strdate, 'PRESEND_TIME'=>$strdate, 'ORGID'=>10001,
			'SEND_USERID'=>$id, 'DESTADDR'=>$mobile, 'MESSAGE'=>$content
		]);
		
		return $id<=0 ? false : true;
	}
	
	
}
