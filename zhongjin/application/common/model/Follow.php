<?php
/**
 * 关注
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Follow extends Model{
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 */
	public function getOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 */
	public function getMore($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->field($field)->where($where)->select();
	}
	
	/**
	 * 统计
	 * @param array  $where 条件
	 */
	public function count($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->where($where)->count();
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
		
		return $this->insert($data);
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
		
		return $this->where($where)->update($data);
	}
}


