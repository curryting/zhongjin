<?php
/**
 * 对通知的操作
 * by sherlock
 */
namespace app\common\model;

use think\Db;
use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Notice extends Model{
	const NOTICE		= 'notice';
	const N_FILE		= 'notice_file';
	const N_MEMBER	= 'notice_member';
	const N_NEWS		= 'notice_news';
	const N_PREVIEW	= 'notice_preview';
	const N_REPLY		= 'notice_reply';
	const N_TEXT		= 'notice_text';
	const N_TASK		= 'notice_timetask';
	const N_TASK_NEWS	= 'notice_timetask_news_child';
	
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
		
		return $this->table(self::NOTICE)->field($field)->where($where)->find();
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
		
		return $this->table(self::NOTICE)->field($field)->where($where)
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
		
		return $this->table(self::NOTICE)->field($field)->where($where)
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
		
		return $this->table(self::NOTICE)->where($where)->update($data);
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
		
		return $this->table(self::NOTICE)->where($where)->count();
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
		
		return $this->table(self::NOTICE)->insert($data, false, true);
	}
	
	/**
	 * 增加一条文件通知数据
	 * @param array $data	数据
	 */
	public function addFileOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_FILE)->insert($data, false, true);
	}
	
	/**
	 * 读取单条文件数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getFileOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_FILE)->field($field)->where($where)->find();
	}
	
	/**
	 * 修改文件数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateFile($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_FILE)->where($where)->update($data);
	}
	
	/**
	 * 读取单条图文数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getNewsOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_NEWS)->field($field)->where($where)->find();
	}
	
	/**
	 * 增加一条图文数据
	 * @param array $data	数据
	 */
	public function addNewsOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_NEWS)->insert($data, false, true);
	}
	
	/**
	 * 增加一条文本数据
	 * @param array $data	数据
	 */
	public function addTextOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TEXT)->insert($data, false, true);
	}
	
	/**
	 * 读取单条文本数据
	 * @param array $where	条件 
	 * @param string $field 需要读取的字段
	 */
	public function getTextOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TEXT)->field($field)->where($where)->find();
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
		
		return $this->table(self::N_MEMBER)->insertAll($data);
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
		
		return $this->table(self::N_MEMBER)->where($where)->count();
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
		
		return $this->table(self::N_MEMBER)->field($field)->where($where)->select();
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
		
		return $this->table(self::N_MEMBER)->field($field)->where($where)
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
		
		return $this->table(self::N_MEMBER)->where($where)->update($data);
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
		
		return $this->table(self::N_REPLY)->field($field)->where($where)->find();
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
		
		return $this->table(self::N_REPLY)->field($field)->where($where)
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
		
		return $this->table(self::N_REPLY)->insert($data, false, true);
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
		
		$sql = 'SELECT count(*) AS `count`,`parent_id` FROM ' .self::N_REPLY. 
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
		
		return $this->table(self::N_PREVIEW)->insert($data, false, true);
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
		
		return $this->table(self::N_PREVIEW)->field($field)->where($where)->find();
	}
	
	/**
	 * 增加一条定时任务数据
	 * @param array $data	数据
	 */
	public function addTaskOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK)->insert($data, false, true);
	}
	
	/**
	 * 增加多条定时任务数据
	 * @param array $data	数据
	 */
	public function addTaskMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK)->insertAll($data);
	}
	
	/**
	 * 修改任务数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateTask($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK)->where($where)->update($data);
	}
	
	/**
	 * 读取多条定时任务数据
	 * @param array  $where		条件 
	 * @param string $field		需要读取的字段
	 * @param string $order		排序字段
	 */
	public function getTaskMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK)->field($field)->where($where)
				->order($order)->select();
	}
	
	/**
	 * 增加一条图文定时任务数据
	 * @param array $data	数据
	 */
	public function addTaskNewsOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK_NEWS)->insert($data, false, true);
	}
	
	/**
	 * 读取多条图文定时任务数据
	 * @param array  $where		条件 
	 * @param string $field		需要读取的字段
	 * @param string $order		排序字段
	 */
	public function getTaskNewsMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::N_TASK_NEWS)->field($field)->where($where)
				->order($order)->select();
	}
}


