<?php
/**
 * 对验证码的操作
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Code extends Model{
	protected $table = 'verify_code';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条数据
	 * $where array 条件 
	 * $field string 需要读取的字段
	 */
	public function getOne($where, $field='*', $sort='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->field($field)->where($where)->order($sort)->find();
	}
	
	/**
	 * 读取多条数据
	 * $where array 条件 
	 * $field string 需要读取的字段
	 * $sort  string 排序字段
	 */
	public function getMore($where, $field='*', $sort='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->field($field)->where($where)->order($sort)->select();
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
		
		return $this->insert($data, false, true);
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
	
	/**
	 * 删除数据
	 * @param array $where	条件
	 */
	public function del($where)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->where($where)->delete();
	}
}


