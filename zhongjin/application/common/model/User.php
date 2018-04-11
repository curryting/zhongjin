<?php
/**
 * 对用户的操作
 * by sherlock
 */
namespace app\common\model;
use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class User extends Model{
	const USER = 'user';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 增加一条数据
	 * @param array $data	数据
	 */
	public function addOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::USER)->insert($data, false, true);
	}
	
	/**
	 * 读取单条数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::USER)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getMore($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::USER)->field($field)->where($where)->select();
	}
	
	/**
	 * 修改数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateMore($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::USER)->where($where)->update($data);
	}
}


