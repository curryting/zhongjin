<?php
namespace app\home\controller;

use app\home\logic\CustomerLogic;
use app\common\logic\FileLogic;
use app\common\controller\Errcode;

class Customer extends Root
{
	/**
	 * 增加客户
	 */
    public function add()
	{
		$data = $this->_getParam();
		
		$res = CustomerLogic::add($data);
		$res === FALSE && $this->result('', Errcode::INSERT_ERR, '添加客户失败');
		$res === -1 && $this->result('', Errcode::USER_ISSET, '用户已存在');
		
		return $this->resultOk(['id'=>$res]);
	}
	
	/**
	 * 修改客户
	 */
	public function update()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$data = $this->_getParam();
		
		$res = CustomerLogic::update($id, $data);
		$res || $this->result('', Errcode::INSERT_ERR, '修改客户失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 删除客户
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = CustomerLogic::del($id);
		$res || $this->result('', Errcode::INSERT_ERR, '删除客户失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 客户列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$type = input('param.client_type', 0, 'intval');
		in_array($type, [0,1,2]) || $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$productId = input('param.product', 0, 'intval');
		$productId <0 && $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = CustomerLogic::lists($type, $productId, $offset, $psize, $keywords);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 客户详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = CustomerLogic::detail($id);
		return $this->resultOk(['info'=>$info]);
	}
	
	/**
	 * 获取客户投资产品
	 */
	public function getInvestProduct()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = CustomerLogic::getInvestProduct($id, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	
	/**
	 * 根据名字模糊获取客户信息
	 */
	public function getName()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		//empty($keywords) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = CustomerLogic::getName($keywords, $offset, $psize);
		return $this->resultOk(['list'=>$info]);
	}
	
	/**
	 * 上传客户文件 
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
	 * 上传报告文件 
	 */
	public function uploadReport()
	{
		try {
			$data = FileLogic::uploadOne('file', [
				'size'=>1024*1024*10, 'ext'=>'txt,pdf,doc,ppt,xls,docx,pptx,xlsx,xlsm'
			]);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 导入客户文件
	 */
	public function import()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try {
			$res = CustomerLogic::import($id);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk();
	}
	
	
	
	/**
	 * 获取参数
	 */
	private function _getParam()
	{
		$type = input('param.client_type', 1, 'intval');
		in_array($type, [1,2]) || $this->result('', Errcode::PARAM_ERR, '客户类型错误');
		
		$name = input('param.name', '', 'htmlspecialchars');
		empty($name) && $this->result('', Errcode::PARAM_ERR, '名称不能为空');
		mb_strlen($name) >20 && $this->result('', Errcode::PARAM_ERR, '名称长度不能超过20');
		
		if($type == 2){
			$companyName = input('param.company_name', '', 'htmlspecialchars');
			empty($companyName) && $this->result('', Errcode::PARAM_ERR, '公司名称名称不能为空');
			mb_strlen($companyName) >32 && $this->result('', Errcode::PARAM_ERR, '公司名称长度不能超过20');
		}
		
		$credentialsType = input('param.id_type', 6, 'intval');
		in_array($credentialsType, [1,2,3,4,5,6]) || $this->result('', Errcode::PARAM_ERR, '证件类型错误');
		
		$credentials = input('param.id_number', '', 'htmlspecialchars');
		empty($credentials) && $this->result('', Errcode::PARAM_ERR, '证件号码不能为空');
		mb_strlen($credentials) >32 && $this->result('', Errcode::PARAM_ERR, '证件号码长度不能超过32');
		
		$mobile = input('param.phone', 6, 'trim');
		empty($mobile) && $this->result('', Errcode::PARAM_ERR, '手机号码不能为空');
		is_mobile($mobile) || $this->result('', Errcode::PARAM_ERR, '手机号码格式错误');
		
		$data = [
			'type'=>$type, 'name'=>$name, 'mobile'=>$mobile, 
			'credentials'=>$credentials, 'credentials_type'=>$credentialsType
		];
		isset($companyName) && $data['company_name'] = $companyName;
		return $data;
	}
}
