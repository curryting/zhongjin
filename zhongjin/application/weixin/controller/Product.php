<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\weixin\logic\ProductLogic;
use app\home\logic\InvestmentLogic;

class Product extends Root
{
	/**
	 * 客户投资的产品名称列表，只显示我自己的 ,管理员显示全部
	 */
	public function lists()
	{
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		if(intval(ADMINID) >0){
			//  管理员
			$info = ProductLogic::listsAll(0, $offset, $psize);
		}else{
			$info = ProductLogic::lists(MEMID, $offset, $psize);
		}
		
		return $this->result(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 客户投资的产品列表， 只显示我自己的
	 */
	public function investLists()
	{
		$productId = input('param.id', 0, 'intval');
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = ProductLogic::investLists(MEMID, $productId, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 投资产品详情
	 */
	public function detail()
	{
		// 投资记录的ID
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = ProductLogic::detail($id);
		return $this->resultOk(['info'=>$info]);
	}
	
	/**
	 * 报告列表
	 */
	public function reportLists()
	{
		$id = input('param.id', 0, 'intval');	//	投资记录ID
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		$report_type = input('param.type', 0, 'intval');
		
		$info = InvestmentLogic::reportLists($id, $offset, $psize, $report_type);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
}
