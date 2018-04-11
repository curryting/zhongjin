<?php
/**
 * 对新闻的操作
 * by sherlock
 */
namespace app\common\model;

use think\Db;
use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class News extends Model{
	const NEWS = 'news';
	const NEWS_INFO = 'news_info';
	const NEWS_REPLY = 'news_reply';
	const NEWS_MEMBER = 'news_member';
	const NEWS_MULTIPLE = 'news_multiple';
	const NEWS_PREVIEW = 'news_preview';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条基本数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getBaseOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条基本数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 * @param string $order	排序
	 */
	public function getBaseMore($where, $field='*', $order='id DESC')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS)->field($field)->where($where)
			->order($order)->select();
	}
	
	/**
	 * 读取多条基本数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 * @param int	$offset	偏移量
	 * @param int	$psize	一页大小
	 * @param string $order	排序
	 */
	public function getBaseMorePage($where, $field='*', $offset=0, $psize=10, $order='id DESC')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS)->field($field)->where($where)
			->limit($offset, $psize)->order($order)->select();
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
		
		return $this->table(self::NEWS)->where($where)->update($data);
	}
	
	/**
	 * 统计
	 * @param array $where	条件
	 */
	public function countBase($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS)->where($where)->count();
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
		
		return $this->table(self::NEWS)->insert($data, false, true);
	}
	
	/**
	 * 读取单条扩展数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getExtraOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_INFO)->field($field)->where($where)->find();
	}
	
	/**
	 * 增加一条扩展数据
	 * @param array $data	数据
	 */
	public function addExtraOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_INFO)->insert($data, false, true);
	}
	
	/**
	 * 增加一条扩展数据
	 * @param array $data	数据
	 */
	public function addMultipeOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MULTIPLE)->insert($data, false, true);
	}
	
	/**
	 * 增加多条成员数据
	 * @param array $data	数据
	 */
	public function addMemberMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MEMBER)->insertAll($data);
	}
	
	/**
	 * 统计
	 * @param array $where	条件
	 */
	public function countMember($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MEMBER)->where($where)->count();
	}
	
	/**
	 * 读取多条基本数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getMemberMore($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MEMBER)->field($field)->where($where)->select();
	}
	
	/**
	 * 读取多条成员数据
	 * @param array  $where		条件 
	 * @param string $field		需要读取的字段
	 * @param int	 $offset	偏移量
	 * @param int	 $psize		一页的大小
	 * @param string $order		排序
	 */
	public function getMemberMorePage($where, $field='*', $offset=0, $psize=10, $order='id DESC')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MEMBER)->field($field)->where($where)
			->limit($offset,$psize)->order($order)->select();
	}
	
	/**
	 * 修改成员数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateMemberMore($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_MEMBER)->where($where)->update($data);
	}
	
	/**
	 * 读取单条评论数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getCommentOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_REPLY)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条评论数据
	 * @param array  $where		条件 
	 * @param string $field		需要读取的字段
	 * @param int	 $offset	偏移量
	 * @param int	 $psize		一页的大小
	 * @param string $order		排序
	 */
	public function getCommentMorePage($where, $field='*', $offset=0, $psize=10, $order='id DESC')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_REPLY)->field($field)->where($where)
			->limit($offset,$psize)->order($order)->select();
	}
	
	/**
	 * 增加一条评论数据
	 * @param array $data	数据
	 */
	public function addCommentOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_REPLY)->insert($data, false, true);
	}
	
	/**
	 * 统计
	 * @param array $parentId	评论的ids数组
	 */
	public function countCommentGroup($parentIds)
	{
		if(empty($parentIds)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		$sql = 'SELECT count(*) AS `count`,`parent_id` FROM ' .self::NEWS_REPLY. 
			' WHERE `parent_id` in(' .implode(',',$parentIds). ') AND `type`=1 GROUP BY `parent_id`;';
		return Db::query($sql);
	}
	
	/**
	 * 增加一条预览数据
	 * @param array $data	数据
	 */
	public function addPreviewOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_PREVIEW)->insert($data, false, true);
	}
	
	/**
	 * 读取单条预览数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getPreviewOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::NEWS_PREVIEW)->field($field)->where($where)->find();
	}
}


