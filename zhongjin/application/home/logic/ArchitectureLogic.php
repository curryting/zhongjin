<?php
/**
 * 组织架构操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use app\common\model\Customer;

class ArchitectureLogic{
	
	/**
	 * 解析发送对象
	 * @param array $sendArr 待解析的对象
	 */
	public static function parseToMember($sendArr=[])
	{
		if(empty($sendArr))
			return [];
		
		$model = new Customer();
		
		$memids = [];
		if(isset($sendArr['category']) && !empty($sendArr['category'])){
			// 读取项目的所有成员
			$productIds = array_values(array_filter(array_unique($sendArr['category'])));
			if(! empty($productIds)){
				$info = $model->getInvestMore(['product_id'=>['in', $productIds], 'status'=>0, 'is_del'=>0], 'memid');
				foreach($info as $v){
					$memids[] = $v['memid'];
				}
			}
		}
		
		if(isset($sendArr['member']) && !empty($sendArr['member'])){
			$_memids = array_values(array_filter(array_unique($sendArr['member'])));
			empty($_memids) || $memids = array_merge($memids, $_memids);
		}
		
		$memids = array_values(array_filter(array_unique($memids)));
		return $memids;
	}
	
	/**
	 * 获取发送对象里面的名字
	 * @param type $sendArr
	 */
	public static function getName($sendArr=[])
	{
		if(empty($sendArr))
			return [];
		
		$name = [];
		if(isset($sendArr['member']) && !empty($sendArr['member'])){
			$_memids = array_values(array_filter(array_unique($sendArr['member'])));
			// 读取名称
			$_memInfo = CustomerLogic::getInfoByMemids($_memids, true, false);
			foreach($_memInfo as $v){
				$name[] = $v['name'];
			}
			unset($_memInfo);
		}
		
		if(isset($sendArr['category']) && !empty($sendArr['category'])){
			$productIds = array_values(array_filter(array_unique($sendArr['category'])));
			// 读取产品名称
			$_proInfo = ProductLogic::getNameByIds($productIds, false);
			foreach($_proInfo as $v){
				$name[] = $v['title'];
			}
			unset($_proInfo);
		}
		
		return $name;
	}
}
