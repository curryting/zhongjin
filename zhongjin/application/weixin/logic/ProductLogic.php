<?php
/**
 * 产品操作逻辑层
 * by sherlock
 */

namespace app\weixin\logic;

use think\Db;
use app\common\model\Product;
use app\common\model\Customer;
use app\common\controller\Json;
use app\common\controller\Errcode;

class ProductLogic{
	
	/**
	 * 客户投资的产品记录
	 * @param int $memid		客户ID
	 * @param int $productId	产品ID 0：读取全部 
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 */
	public static function investLists($memid=MEMID, $productId=0, $offset=0, $psize=10)
	{
		$model = new Customer();
		
		$where = ['is_del'=>0];
		if($memid > 0){
			$where['memid'] = $memid;
			$where['status'] = 0;
		}
		$productId == 0 || $where['product_id'] = $productId;
		
		$count = $model->getInvestCount($where);
		$info = $model->getInvestMorePage($where, 'id,product_id,money,time', 
			$offset, $psize, 'id DESC');
		
		$productIds = [];
		foreach($info as $v){
			$productIds[] = $v['product_id'];
		}
		
		$productIds = array_values(array_filter(array_unique($productIds)));
		
		if(! empty($productIds)){
			$model = new Product();
			$_productInfo = $model->getBaseMore(['id'=>['in', $productIds], 'is_del'=>0], 'id,title');
			foreach($_productInfo as $v){
				$productInfo[$v['id']] = $v['title'];
			}
			unset($_productInfo);
			
			foreach($info as &$v){
				isset($productInfo[$v['product_id']]) && $v['title'] = $productInfo[$v['product_id']];
				$v['time'] = date('Y-m-d', $v['time']);
			}
		}

		return [empty($info) ? []:$info, $count];
	}
	
	/**
	 * 获取详情
	 * @param int $id		投资记录ID
	 */
	public static function detail($id)
	{
		$model = new Customer();
		
		$investInfo = $model->getInvestOne(['id'=>$id, 'is_del'=>0], 'money,id,product_id');
		if(! empty($investInfo)){
			// 查看有没有报告
			$reportInfo = $model->getReportMore(['invest_id'=>$id], 'id', 0, 1);
			
			// 查找产品的具体详情
			$model = new Product();
			$baseInfo = $model->getBaseOne(['id'=>$investInfo['product_id']]);
			$extraInfo = $model->getExtraOne(['product_id'=>$investInfo['product_id']]);
			
			$info = [
				'report'=>empty($reportInfo) ? 0 : 1,
				'status'		=> $baseInfo['status'],	
				'id'=>$investInfo['id'], 'money'=>$investInfo['money'],
				'name'			=> htmlspecialchars_decode($baseInfo['title']),
				'scope'			=> htmlspecialchars_decode($extraInfo['invest_range']),
				'establish'	=> date('Y-m-d', $extraInfo['establish_time']),
				'manager'		=> htmlspecialchars_decode($extraInfo['manager']),
				'deadline'		=> htmlspecialchars_decode($extraInfo['deadline']),
				'currency'		=> htmlspecialchars_decode($extraInfo['currency']),
				'trust_fee'	=> htmlspecialchars_decode($extraInfo['trusteeship_fee']),
				'trusteeship'	=> htmlspecialchars_decode($extraInfo['custodian']),
				'subscription_fee'	=> htmlspecialchars_decode($extraInfo['subscription_fee']),
				'management_fee'		=> htmlspecialchars_decode($extraInfo['management_fee']), 
				'outsourcing_fee'		=> htmlspecialchars_decode($extraInfo['outsourcing_service_fee']),
				'redemption_fee'		=> htmlspecialchars_decode($extraInfo['redemption_fee']),
			];
		}
		unset($baseInfo);
		unset($investInfo);
		unset($extraInfo);

		return isset($info) ? $info:[];
	}
	
	/**
	 * 客户投资的所有产品
	 * @param int $memid		客户ID
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 */
	public static function lists($memid=MEMID, $offset=0, $psize=10)
	{
		$model = new Customer();
		
		$count = $model->getInvestCount(['memid'=>$memid, 'status'=>0, 'is_del'=>0]);
		$_investInfo = $model->getInvestGroup(['memid'=>$memid, 'status'=>0, 'is_del'=>0], 
			'product_id', $offset, $psize, 'product_id', 'id DESC');
		$productIds = [];
		foreach($_investInfo as $v){
			$productIds[] = $v['product_id'];
		}
		unset($_investInfo);
		
		$productIds = array_values(array_filter(array_unique($productIds)));
		if(! empty($productIds)){
			$model = new Product();
			$info = $model->getBaseMore(['id'=>['in', $productIds], 'is_del'=>0], 'id,title');
		}
		
		return [isset($info) ? $info : [], $count];
	}
	
	/**
	 * 产品列表
	 * @param int $status		状态 0：读取全部 1:募集中2:存续3:退出 4:不读取退出的产品
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 */
	public static function listsAll($status=0, $offset=0, $psize=10)
	{
		$model = new Product();
		$where = ['is_del'=>0];
		empty($keywords) || $where['title'] = ['like', "%$keywords%"];
		switch($status){
			case 1:
			case 2:
			case 3:
				$where['status'] = $status;
				break;
			case 4:
				$where['status'] = ['in', [1,2]];
				break;
			default:
				break;
		}
		
		if($offset >= 0){
			$list = $model->getBaseMorePage($where, $offset, $psize, 'id,title,status', 'id DESC');
			$count = $model->countBase($where);
		}else{
			// 查询全部
			$list = $model->getBaseMore($where, 'id,title,status', 'id DESC');
			$count = 0;
		}

		return [empty($list) ? []:$list, $count];
	}
}
