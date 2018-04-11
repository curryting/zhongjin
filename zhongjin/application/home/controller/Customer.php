<?php
namespace app\home\controller;

use app\home\logic\CustomerLogic;
use app\common\logic\FileLogic;
use app\common\controller\Errcode;
use app\home\logic\WxLogic;

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
		$res === -2 && $this->result('', Errcode::USER_ISSET, '该号码已经是管理员不能添加成客户');
		
		try{
			WxLogic::contact_sync_qy([$res], 'sync');
		}catch(\Exception $e){
			CustomerLogic::del($res);
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
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
		
		try{
			WxLogic::contact_sync_qy([$id], 'sync');
		}catch(\Exception $e){
			$this->result('', $e->getCode(), $e->getMessage());
		}
		
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
	 * 根据名字模糊获取客服信息 必须是已经投了产品的客户
	 */
	public function getNameJoinInvestment()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		
		$info = CustomerLogic::getNameJoinInvestment($keywords);
		return $this->resultOk(['list'=>$info]);
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
	 * 获取一个项目下的客户 支持分页
	 */
	public function getNameByProduct()
	{
		$id = input('param.id', 0, 'intval'); // 产品ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = CustomerLogic::getNameByProduct($id, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
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
	 * 导入客户文件
	 */
	public function import()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try {
			$res = CustomerLogic::import($id);
			WxLogic::contact_sync_qy($res, 'sync');
		} catch (\Exception $e) {
			trace('import customer err : code:' .$e->getCode(). ',msg: '.$e->getMessage());
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk(['info'=>[]]);
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
		mb_strlen($name,'UTF-8') >20 && $this->result('', Errcode::PARAM_ERR, '名称长度不能超过20');
		
		if($type == 2){
			$companyName = input('param.company_name', '', 'htmlspecialchars');
			empty($companyName) && $this->result('', Errcode::PARAM_ERR, '公司名称名称不能为空');
			mb_strlen($companyName,'UTF-8') >32 && $this->result('', Errcode::PARAM_ERR, '公司名称长度不能超过20');
		}
		
		$credentialsType = input('param.id_type', 6, 'intval');
		in_array($credentialsType, [1,2,3,4,5,6]) || $this->result('', Errcode::PARAM_ERR, '证件类型错误');
		
		$credentials = input('param.id_number', '', 'htmlspecialchars');
		empty($credentials) && $this->result('', Errcode::PARAM_ERR, '证件号码不能为空');
		
		switch($credentialsType){
			case 1: // 身份证
				$res = preg_match('/[0-9]{17}[1-9xX]/i', $credentials);
				$res == 0 && $this->result('', Errcode::PARAM_ERR, '身份证格式为18位纯数字或者17位数字+X');
				break;
			case 2: // 营业执照
				$len = strlen($credentials);
				$res = preg_match('/[0-9]{15}/i', $credentials);
				($res == 0 || $len != 15)&& $this->result('', Errcode::PARAM_ERR, '营业执照为15位纯数字');
				break;
			case 3: // 港澳通行证
				$res = preg_match('/^[CHW][0-9]{8}/i', $credentials);
				$len = strlen($credentials);
				($res == 0 || $len != 9) && $this->result('', Errcode::PARAM_ERR, '港澳通行证以C、H、W开头+8位纯数字');
				break;
			case 4: // 护照
				$res = preg_match('/^([EGPSDHM]|14|15)[1-9]*/i', $credentials);
				$res == 0 && $this->result('', Errcode::PARAM_ERR, '护照格式不对');
				break;
			case 5: // 台胞回乡证
				$res1 = preg_match('/^[0-9]{8}/i', $credentials);
				$res2 = preg_match('/^[0-9]{10}[ABC]/i', $credentials);
				($res1 == 0 && $res2 == 0) && $this->result('', Errcode::PARAM_ERR, '台胞回乡证为8位数字或者10位数字+A、B、C结尾');
				break;
			case 6:
				mb_strlen($credentials,'UTF-8') >32 && $this->result('', Errcode::PARAM_ERR, '证件号码长度不能超过32');
				break;
		}

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
