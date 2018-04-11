<?php
/**
 * 产品操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use think\Db;
use app\common\model\Product;
use app\common\model\Customer;
use app\common\controller\Json;
use app\common\controller\Errcode;

class ProductLogic{
	
	/**
	 * 增加一个产品
	 * @param array $data	一个产品的所有信息
	 */
	public static function add(&$data)
	{
		$model = new Product();
		$info = $model->getBaseOne(['title'=>$data['title']], 'id');
		if(! empty($info)){
			return -1;
		}
		
		//开启事务
		Db::startTrans();
		try {
			$id = $model->addBaseOne([
				'title'=>$data['title'], 'status'=>$data['status'],
				'create_time'=> NOW_TIME
			]);
			unset($data['title']);
			unset($data['status']);
			$data['product_id'] = $id;
			$model->addExtraOne($data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}

		return $id;
	}
	
	/**
	 * 修改
	 * @param int $id		产品ID
	 * @param array $data	一个产品的所有信息
	 */
	public static function update($id, &$data)
	{
		$model = new Product();
		//开启事务
		Db::startTrans();
		try {
			$model->updateBase(['id'=>$id], [
				'title'=>$data['title'], 'status'=>$data['status'],
				'update_time'=>NOW_TIME
			]);
			$data['status'] == 3 && $data['delisting_time'] = NOW_TIME;
			unset($data['title']);
			unset($data['status']);
			$model->updateExtra(['product_id'=>$id], $data);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		return true;
	}
	
	/**
	 * 产品列表
	 * @param int $status		状态 0：读取全部 1:募集中2:存续3:退出 4:不读取退出的产品
	 * @param int $offset		偏移量
	 * @param int $psize		一页的大小
	 * @param string $keywords	关键字
	 */
	public static function lists($status=0, $offset=0, $psize=10, $keywords='')
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
	
	/**
	 * 获取详情
	 * @param int $id		产品ID
	 */
	public static function detail($id)
	{
		$model = new Product();
		
		$baseInfo = $model->getBaseOne(['id'=>$id]);
		$extraInfo = $model->getExtraOne(['product_id'=>$id]);
		
		if(!empty($baseInfo) && !empty($extraInfo)){
			$info = [
				'id'=>$baseInfo['id'], 'status'=>$baseInfo['status'], 
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
			unset($baseInfo);
			unset($extraInfo);
		}
		return isset($info) ? $info:[];
	}
	
	/**
	 * 删除
	 * @param int $id	记录ID
	 */
	public static function del($id)
	{
		$productModel = new Product();
		$customerModel = new Customer();
		
		//开启事务
		Db::startTrans();
		try {
			$productModel->updateBase(['id'=>$id], ['is_del'=>1]);
			$customerModel->updateInvest(['product_id'=>$id], ['is_del'=>1]);
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		
		return true;
	}
	
	/**
	 * 根据ids获取产品名称
	 * @param array $ids		产品ids
	 * @param bool  $isReadDel	是否读已经删除的产品名称
	 */
	public static function getNameByIds($ids, $isReadDel=true)
	{
		$ids = array_values(array_filter(array_unique($ids)));
		
		$model = new Product();
		$where = ['id'=>['in', $ids]];
		$isReadDel || $where['is_del'] = 0;
		$list = $model->getBaseMore($where, 'id,title');
		
		return empty($list) ? [] : $list;
	}
}
