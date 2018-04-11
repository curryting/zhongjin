<?php
/**
 * 记录信息
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\Record;

class RecordLogic{
	
	/**
	 * 记录发送消息的条数
	 * @param int $adminid		管理员ID
	 * @param int $appid		应用ID
	 * @param int $count		发送的条数
	 */
	public static function sendMsgCount($adminid, $appid, $count)
	{
		$model = new Record();
		
		$id = $model->addMsgOne([
			'create_time'	=> NOW_TIME,
			'adminid'		=> $adminid,
			'appid'			=> $appid,
			'counts'		=> $count
		]);
		
		return $id >0 ? true : false;
	}
	
	/**
	 * 增加一条登录记录
	 * @param array $data 待增加的数据
	 */
	public static function addLogin($data)
	{
		$model = new Record();
		
		$res = $model->addLoginOne($data);
		return $res >0 ? true : false;
	}
	
	/**
	 * 统计最近7天的发消息量
	 */
	public static function countLastSevenDay()
	{
		$model = new Record();
		
		$time = NOW_TIME - 7*24*3600;
		return $model->sumMsg(['create_time'=> ['gt', $time]], 'counts');
	}
	
	/**
	 * 获取昨天的活跃数和登录数
	 */
	public static function activeAndLogin()
	{
		$model = new Record();
		
		$time = strtotime('-1 day');
		$time = date('Y-m-d', $time);
		$time = strtotime($time);
		$activeCount = $model->countRecord(['create_time'=>$time], 'memid');
		$loginCount = $model->countRecord(['create_time'=>$time]);
		
		return [$activeCount, $loginCount];
	}
	
}
