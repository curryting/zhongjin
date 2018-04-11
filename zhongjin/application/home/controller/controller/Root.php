<?php
namespace app\home\controller;
use app\common\controller\Common;

class Root extends Common{
	protected function _initialize()
	{
		parent::_initialize();
		$this->authPc();
	}

	/**
	 * 正常数据返回加工
	 * @param array $data
	 */
	protected function resultOk($data=[])
	{
		$data = ['data'=>$data, 'code'=>200];
		return $data;
	}
	
	/**
	 * 错误数据返回加工
	 * @param array $data
	 */
	protected function resultErr($msg, $code=404)
	{
		$data = ['msg'=>$msg, 'code'=>$code];
		return $data;
	}
}


