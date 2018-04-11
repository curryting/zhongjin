<?php
namespace app\home\controller;

use app\home\logic\ProductLogic;
use app\common\controller\Errcode;

class Product extends Root
{
	/**
	 * 增加产品
	 */
	public function add()
	{
		$data = $this->_getParam();
		$res = ProductLogic::add($data);
		$res == false && $this->result('', Errcode::INSERT_ERR, '添加失败');
		
		return $this->resultOk(['id'=>$res]);
	}
	
	/**
	 * 产品列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$status = input('param.status', 0, 'intval');
		in_array($status, [0,1,2,3]) || $this->result('', Errcode::PARAM_ERR, '产品状态错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = ProductLogic::lists($status, $offset, $psize, $keywords);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 修改
	 */
	public function update()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$data = $this->_getParam();
		 
		$res = ProductLogic::update($id, $data);
		$res == false && $this->result('', Errcode::INSERT_ERR, '添加失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		return $this->resultOk(ProductLogic::detail($id));
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = ProductLogic::del($id);
		$res || $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 获取参数
	 */
	private function _getParam()
	{
		$title = input('param.name', '', 'htmlspecialchars');
		empty($title) && $this->result('', Errcode::PARAM_ERR, '名称不能为空');
		mb_strlen($title) >20 && $this->result('', Errcode::PARAM_ERR, '名称长度不能超过20');
		
		$establishTime = input('param.establish', '', 'strtotime');
		empty($establishTime) && $this->result('', Errcode::PARAM_ERR, '成立日期或到期日期不能为空');
		
		$deadline = input('param.deadline', '', 'htmlspecialchars');
		empty($deadline) && $this->result('', Errcode::PARAM_ERR, '产品期限不能为空');
		mb_strlen($deadline) >20 && $this->result('', Errcode::PARAM_ERR, '产品期限长度不能超过20');
		
		$investRange = input('param.scope', '', 'htmlspecialchars');
		empty($investRange) && $this->result('', Errcode::PARAM_ERR, '投资范围不能为空');
		mb_strlen($investRange) >20 && $this->result('', Errcode::PARAM_ERR, '投资范围长度不能超过20');
		
		$status = input('param.status', 0, 'intval');
		in_array($status, [1,2,3]) || $this->result('', Errcode::PARAM_ERR, '产品状态错误');
		
		$manager = input('param.manager', '', 'htmlspecialchars');
		empty($manager) && $this->result('', Errcode::PARAM_ERR, '管理人不能为空');
		mb_strlen($manager) >20 && $this->result('', Errcode::PARAM_ERR, '管理人长度不能超过20');
		
		$currency = input('param.currency', '', 'htmlspecialchars');
		empty($currency) && $this->result('', Errcode::PARAM_ERR, '币种不能为空');
		mb_strlen($currency) >20 && $this->result('', Errcode::PARAM_ERR, '币种长度不能超过20');
		
		$subscriptionFee = input('param.subscription_fee', '', 'htmlspecialchars');
		empty($subscriptionFee) && $this->result('', Errcode::PARAM_ERR, '认购费不能为空');
		mb_strlen($currency) >20 && $this->result('', Errcode::PARAM_ERR, '认购费长度不能超过20');
		
		$managementFee = input('param.management_fee', '', 'htmlspecialchars');
		empty($managementFee) && $this->result('', Errcode::PARAM_ERR, '管理费不能为空');
		mb_strlen($managementFee) >20 && $this->result('', Errcode::PARAM_ERR, '管理费长度不能超过20');
		
		$redemptionFee = input('param.redemption_fee', '', 'htmlspecialchars');
		empty($redemptionFee) && $this->result('', Errcode::PARAM_ERR, '赎回费不能为空');
		mb_strlen($redemptionFee) >20 && $this->result('', Errcode::PARAM_ERR, '赎回费长度不能超过20');
		
		$custodian = input('param.trusteeship', '', 'htmlspecialchars');
		$outsourcingFee = input('param.outsourcing_fee', '', 'htmlspecialchars');
		$trusteeshipFee = input('param.trust_fee', '', 'htmlspecialchars');
		
		return [
			'title'=>$title, 'establish_time'=>$establishTime, 'deadline'=>$deadline,
			'invest_range'=>$investRange, 'manager'=>$manager, 'currency'=>$currency,
			'custodian'=>$custodian, 'subscription_fee'=>$subscriptionFee, 'status'=>$status,
			'management_fee'=>$managementFee, 'outsourcing_service_fee'=>$outsourcingFee,
			'redemption_fee'=>$redemptionFee, 'trusteeship_fee'=>$trusteeshipFee
		];
	}
}
