<?php
/**
 * 客户相关表(客户表 投资表)
 * by sherlock
 */
namespace app\common\model;

use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Customer extends Model{
	const CUSTOMER = 'member';
	const INVESTMENT = 'investment';
	const REPORT	= 'report';
	
	protected function initialize()
    {
        parent::initialize();
	}
	
	/**
	 * 读取单条的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 */
	public function getCustomerOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 */
	public function getCustomerMore($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->field($field)->where($where)->select();
	}
	
	/**
	 * 分页读取多条条的基本数据
	 * @param array $where	条件
	 * @param int $offset	偏移量
	 * @param int $psize	一页的大小
	 * @param string $field	需要读取的字段
	 * @param string $order	排序字段
	 */
	public function getCustomerMorePage($where, $offset=0, $psize=10, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->field($field)->where($where)
			->order($order)->limit($offset, $psize)->select();
	}

	/**
	 * 读取符合条件的数据有多少条
	 * @param array $where	条件
	 */
	public function countCustomer($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->where($where)->count();
	}
	
	/**
	 * 增加一条客户数据
	 * @param array $data	数据
	 */
	public function addCustomerOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->insert($data, false, true);
	}
	
	/**
	 * 增加多条客户数据 对于表插入的数据不一致的情况下此函数不能用
	 * @param array $data	数据
	 */
	public function addCustomerMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->insertAll($data);
	}
	
	/**
	 * 修改客户数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateCustomer($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::CUSTOMER)->where($where)->update($data);
	}
	
	/**
	 * 增加一条投资数据
	 * @param array $data	数据
	 */
	public function addInvestmentOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->insert($data, false, true);
	}
	
	/**
	 * 增加多条投资数据
	 * @param array $data	数据
	 */
	public function addInvestmentMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->insertAll($data);
	}
	
	/**
	 * 连接查询两表的数据
	 * @param array $where	条件
	 * @param array $join	连接条件
	 * @param string $field	字段
	 * @param int $offset	偏移量
	 * @param int $limit	一页大小
	 * @param string $order	排序
	 */
	public function getJoinCustomInvest($where, $join, $field='*', 
		$offset=0, $limit=10, $order='a.id DESC', $group='')
	{
		if(empty($where) || empty($join)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		if(empty($group)){
			return $this->table(self::INVESTMENT)->alias('a')->join($join)
				->field($field)->where($where)->limit($offset, $limit)
				->order($order)->select();
		}else{
			return $this->table(self::INVESTMENT)->alias('a')->join($join)
				->field($field)->where($where)->limit($offset, $limit)
				->order($order)->group($group)->select();
		}
	}
	
	/**
	 * 连接查询两表的满足条件总条数
	 * @param array $where	条件
	 * @param array $join	连接条件
	 */
	public function getJoinCustomInvestCount($where, $join)
	{
		if(empty($where) || empty($join)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->alias('a')->join($join)
			->where($where)->count();
	}
	
	/**
	 * 修改投资表数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateInvest($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->where($where)->update($data);
	}
	
	/**
	 * 读取投资的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 */
	public function getInvestOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多个投资的排序基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 * @param int	$offset 偏移量
	 * @param int	$psize	一页大小
	 * @param string $group 组别
	 * @param string $order 排序
	 */
	public function getInvestGroup($where, $field='*', $offset=0, $psize=10, $group='memid', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->field($field)->where($where)
			->limit($offset,$psize)->group($group)->order($order)->select();
	}
	
	/**
	 * 分页读取多个投资的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 * @param int	$offset 偏移量
	 * @param int	$psize	一页大小
	 * @param string $order 排序
	 */
	public function getInvestMorePage($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->field($field)->where($where)
			->limit($offset,$psize)->order($order)->select();
	}
	
	/**
	 * 读取多个投资的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getInvestMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->field($field)->where($where)
			->order($order)->select();
	}
	
	/**
	 * 连接投资的满足条件总条数
	 * @param array $where	条件
	 */
	public function getInvestCount($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::INVESTMENT)->where($where)->count();
	}
	
	/**
	 * 增加一条投资数据
	 * @param array $data	数据
	 */
	public function addReportOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::REPORT)->insert($data, false, true);
	}
	
	/**
	 * 读取多个报告的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 * @param int	$offset 偏移量
	 * @param int	$psize	一页大小
	 * @param string $order 排序
	 */
	public function getReportMore($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::REPORT)->field($field)->where($where)
			->limit($offset,$psize)->order($order)->select();
	}
	
	/**
	 * 读取多个报告的基本数据
	 * @param array $where	条件
	 * @param string $field 需要读取的字段
	 * @param string $group 分组
	 * @param string $order 排序
	 */
	public function getReportMoreGroup($where, $field='*', $group='id', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::REPORT)->field($field)->where($where)
				->group($group)->order($order)->select();
	}
	
	/**
	 * 一个投资的满足条件报告总条数
	 * @param array $where	条件
	 */
	public function getReportCount($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::REPORT)->where($where)->count();
	}
	
}


