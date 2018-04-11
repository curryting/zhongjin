<?php
/**
 * 产品
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Product extends Model{
	const PRODUCT = 'product';
	const PRODUCT_INFO = 'product_info';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 */
	public function getBaseOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->field($field)->where($where)->find();
	}
	
	/**
	 * 分页读取多条条的基本数据
	 * @param array $where	条件
	 * @param int $offset	偏移量
	 * @param int $psize	一页的大小
	 * @param string $field	需要读取的字段
	 * 
	 */
	public function getBaseMorePage($where, $offset=0, $psize=10, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->field($field)->where($where)
			->order($order)->limit($offset, $psize)->select();
	}
	
	/**
	 * 读取多条的基本数据
	 * @param array $where	条件
	 * @param string $field	需要读取的字段
	 * @param string $order 排序字段
	 */
	public function getBaseMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->field($field)->where($where)
			->order($order)->select();
	}
	
	/**
	 * 读取符合条件的数据有多少条
	 * @param array $where	条件
	 */
	public function countBase($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->where($where)->count();
	}
	
	/**
	 * 增加一条基本数据
	 * @param array $data	数据
	 */
	public function addBaseOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->insert($data, false, true);
	}
	
	/**
	 * 修改基本数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateBase($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT)->where($where)->update($data);
	}
	
	/**
	 * 读取单条的附加数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 */
	public function getExtraOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT_INFO)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条的基本数据
	 * @param array $where	条件
	 * @param string $field	需要读取的字段
	 * @param string $order 排序字段
	 */
	public function getExtraMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT_INFO)->field($field)->where($where)
			->order($order)->select();
	}
	
	/**
	 * 增加一条附加数据
	 * @param array $data	数据
	 */
	public function addExtraOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT_INFO)->insert($data, false, true);
	}
	
	/**
	 * 修改附加数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateExtra($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::PRODUCT_INFO)->where($where)->update($data);
	}
}


