<?php
/**
 * 文件
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class File extends Model{
	
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
		return $this->field($field)->where($where)->find();
	}
	
	/**
	 * 写入一条数据
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
	 * 读取多个
	 * @param array $where	条件
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
}


