<?php
/**
 * 微信应用
 * by sherlock
 */

namespace app\common\logic;

use app\common\model\Agent;
use app\common\controller\Errcode;

class AgentLogic{
	const CONTACT_AID = 2000002;
	
	/**
	 * 读取应用
	 * @param int $aid		应用ID
	 * @param string $field 需要返回的数据字段
	 */
	public static function get($aid, $field)
	{
		$model = new Agent();
		
		$find = $model->getOne(['agentid'=>$aid], $field);
		return empty($find) ? [] : $find;
	}
	
	/**
	 * 根据类型获取对应的aid
	 * @param string $type	类型名
	 */
	public static function getAidByType($type)
	{
		$model = new Agent();
		
		$find = $model->getOne(['type'=>$type], 'agentid');
		return empty($find) ? 0 : $find['agentid'];
	}
}
