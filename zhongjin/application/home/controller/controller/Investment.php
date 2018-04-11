<?php
namespace app\home\controller;

use app\home\logic\InvestmentLogic;
use app\common\logic\FileLogic;
use app\common\controller\Errcode;

class Investment extends Root
{
	/**
	 * 增加
	 */
    public function add()
    {
		$memid = input('param.id', 0, 'intval');
		$memid <=0 && $this->result('', Errcode::PARAM_ERR, '客户信息错误');
		
		$productId = input('param.product', 0, 'intval');
		$productId <=0 && $this->result('', Errcode::PARAM_ERR, '产品信息错误');
		
		$name = input('param.bank_name', '', 'htmlspecialchars');
		empty($name) && $this->result('', Errcode::PARAM_ERR, '银行不能为空');
		mb_strlen($name) > 16 && $this->result('', Errcode::PARAM_ERR, '银行长度不能超过16');
		
		$account = input('param.bank_account', 0, 'intval');
		$account == 0 && $this->result('', Errcode::PARAM_ERR, '银行账号只能为数字');
		strlen($account) > 24 && $this->result('', Errcode::PARAM_ERR, '银行账号长度不能超过24');
		
		$money = input('param.money', 0.00, 'floatval');
		$money == 0.00 && $this->result('', Errcode::PARAM_ERR, '金钱不能为空或者为0');
		
		$time = input('param.invest_date', '', 'strtotime');
		empty($time) && $this->result('', Errcode::PARAM_ERR, '投资日期不能为空');
		
		$res = InvestmentLogic::add([
			'memid'=>$memid, 'product_id'=>$productId, 'bank'=>$name,
			'bank_account'=>$account, 'money'=>$money, 'time'=>$time
		]);
		$res === false && $this->result('', Errcode::INSERT_ERR, '添加投资失败');
		
		return $this->resultOk(['id'=>$res]);
    }
	
	/**
	 * 列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$type = input('param.client_type', 0, 'intval');
		in_array($type, [0,1,2]) || $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$productId = input('param.product', 0, 'intval');
		$productId <0 && $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$stime = input('param.start_time', '', 'strtotime');
		$etime = input('param.end_time', '', 'strtotime');
		if(!empty($stime) && !empty($etime)){
			$stime > $etime && $this->result('', Errcode::PARAM_ERR, '开始时间不能大于结束时间');
		}
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = InvestmentLogic::lists($type, $productId, $offset, $psize, $keywords, $stime, $etime);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = InvestmentLogic::del($id);
		$res === false && $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 查找基本信息
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$data = InvestmentLogic::detail($id);
		return $this->resultOk(['info'=>$data]);
	}
	
	/**
	 * 赎回投资
	 */
	public function redeem()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = InvestmentLogic::redeem($id);
		$res === false && $this->result('', Errcode::INSERT_ERR, '导入失败');
		return $this->resultOk();
	}
	
	/**
	 * 转让投资
	 */
	public function transfer()
	{
		$id = input('param.id', 0, 'intval');
		$memid = input('param.memid', 0, 'intval');
		($id <=0 || $memid <=0) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		
		
		
	}
	
	/**
	 * 上传文件
	 */
	public function upload()
	{
		try {
			$data = FileLogic::uploadOne('file', ['size'=>1024*1024, 'ext'=>'xls,xlsx']);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 导入文件
	 */
	public function import()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try {
			$res = InvestmentLogic::import($id);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		$res === false && $this->result('', Errcode::INSERT_ERR, '导入失败');
		return $this->resultOk();
	}
}
