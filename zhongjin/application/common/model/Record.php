<?php
/**
 * 记录表
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Record extends Model{
	const MSG_STATIS = 'wx_msg_statis';
	const RECORD_LOGIN = 'record_login';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 增加一条微信消息条数数据
	 * @param array $data	数据
	 */
	public function addMsgOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::MSG_STATIS)->insert($data, false, true);
	}
	
	/**
	 * 增加一条登录数据
	 * @param array $data	数据
	 */
	public function addLoginOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::RECORD_LOGIN)->insert($data, false, true);
	}
	
	/**
	 * 统计
	 * @param array $where	条件
	 */
	public function sumMsg($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::MSG_STATIS)->where($where)->sum($field);
	}
	
	/**
	 * 统计登录信息
	 * @param array $where		条件
	 * @param string $group		分组
	 */
	public function countRecord($where, $group='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		if(empty($group)){
			return $this->table(self::RECORD_LOGIN)->where($where)->count();
		}else{
			return $this->table(self::RECORD_LOGIN)->where($where)->group($group)->count();
		}
	}
}


