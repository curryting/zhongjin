<?php
/**
 * 公司简介
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Profile extends Model{
	protected $table = 'company_profile';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条数据
	 * $field string 需要读取的字段
	 */
	public function getOne($field='*')
	{
		return $this->field($field)->find();
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
}


