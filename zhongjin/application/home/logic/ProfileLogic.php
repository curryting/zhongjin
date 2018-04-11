<?php
/**
 * 用户操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use app\common\model\Profile;
use app\common\controller\Json;
use app\common\controller\Errcode;

class ProfileLogic{
	
	/**
	 * 更新公司简介
	 * @param string $content	内容
	 */
	public static function update($content)
	{
		$model = new Profile();
		
		$find = $model->getOne('id');
		if(empty($find)){
			$res = $model->addOne(['update_time'=>NOW_TIME, 'desc'=>$content]);
		}else{
			$res = $model->updateMore(['id'=>$find['id']], ['update_time'=>NOW_TIME, 'desc'=>$content]);
		}
		
		return $res <=0 ? false : true;
	}
	
	/**
	 * 读取公司简介
	 */
	public static function get()
	{
		$model = new Profile();
		
		$find = $model->getOne('desc');
		return empty($find) ? '' : $find['desc'];
	}
}
