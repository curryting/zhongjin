<?php
/**
 * 投票
 * by sherlock
 */
namespace app\common\model;

use think\Db;
use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Vote extends Model{
	const VOTE = 'vote';
	const V_EXTRA = 'vote_info';
	const V_QUESTION = 'vote_question';
	const V_ANSWER = 'vote_answer';
	const V_MEMBER = 'vote_member';
	
	protected function initialize()
    {
        parent::initialize();
    }
	
	/**
	 * 读取单条基础数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 */
	public function getBaseOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::VOTE)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条基础数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getBaseMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::VOTE)->field($field)
				->where($where)->order($order)->select();
	}
	
	/**
	 * 分页读取多条基础数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param string $order 排序
	 */
	public function getBaseMorePage($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::VOTE)->field($field)->limit($offset, $psize)
				->where($where)->order($order)->select();
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
		
		return $this->table(self::VOTE)->where($where)->count();
	}
	
	/**
	 * 增加一条基础数据
	 * @param array $data	数据
	 */
	public function addBaseOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::VOTE)->insert($data, false, true);
	}
	
	/**
	 * 修改基础数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateBase($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::VOTE)->where($where)->update($data);
	}
	
	/**
	 * 读取单条扩展数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 */
	public function getExtraOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_EXTRA)->field($field)->where($where)->find();
	}
	
	/**
	 * 分页读取多条扩展数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param string $order 排序
	 */
	public function getExtraMorePage($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_EXTRA)->field($field)->limit($offset, $psize)
				->where($where)->order($order)->select();
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
		
		return $this->table(self::V_EXTRA)->insert($data, false, true);
	}
	
	/**
	 * 修改扩展数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateExtra($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_EXTRA)->where($where)->update($data);
	}
	
	/**
	 * 增加多条问题数据
	 * @param array $data	数据
	 */
	public function addQuestionMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_QUESTION)->insertAll($data);
	}
	
	/**
	 * 增加一条问题数据
	 * @param array $data	数据
	 */
	public function addQuestionOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_QUESTION)->insert($data, false, true);
	}
	
	/**
	 * 修改调研问题数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateQuestion($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_QUESTION)->where($where)->update($data);
	}
	
	/**
	 * 读取单条问题数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 */
	public function getQuestionOne($where, $field='*')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_QUESTION)->field($field)->where($where)->find();
	}
	
	/**
	 * 读取多条问题数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getQuestionMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_QUESTION)->field($field)->where($where)
				->order($order)->select();
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
		
		return $this->table(self::V_MEMBER)->insertAll($data);
	}
	
	/**
	 * 修改成员数据
	 * @param array $where	条件
	 * @param array $data	数据
	 */
	public function updateMember($where, $data)
	{
		if(empty($where) || empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_MEMBER)->where($where)->update($data);
	}
	
	/**
	 * 分页读取多条成员数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param string $order 排序
	 */
	public function getMemberMorePage($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_MEMBER)->field($field)->limit($offset, $psize)
				->where($where)->order($order)->select();
	}
	
	/**
	 * 读取单条成员数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getMemberOne($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_MEMBER)->field($field)
				->where($where)->order($order)->find();
	}
	
	/**
	 * 读取多条成员数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getMemberMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_MEMBER)->field($field)
				->where($where)->order($order)->select();
	}
	/**
	 * 统计成员数量
	 * @param array $where	条件
	 */
	public function countMember($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_MEMBER)->where($where)->count();
	}
	
	/**
	 * 统计投票有多少人回答
	 * @param array $voteIds 调研IDs
	 */
	public function countMemberNum($voteIds)
	{
		if(empty($voteIds)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		$sql = 'SELECT count(*) AS `count`,`vote_id` FROM ' .self::V_MEMBER. 
			' WHERE `vote_id` in(' .implode(',',$voteIds). ') AND `is_answer`=1 AND `is_del`=0 GROUP BY vote_id;';
		return Db::query($sql);
	}
	
	/**
	 * 统计投票有多少人回答
	 * @param array $voteIds 调研IDs
	 */
	public function countAnswerNum($voteIds)
	{
		if(empty($voteIds)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		$sql = 'SELECT count(*) AS `count`,`vote_id` FROM ' .self::V_ANSWER. 
			' WHERE `vote_id` in(' .implode(',',$voteIds). ');';
		return Db::query($sql);
	}
	
	/**
	 * 统计回答数量
	 * @param array $where	条件
	 */
	public function countAnswer($where)
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_ANSWER)->where($where)->count();
	}
	
	/**
	 * 增加一条答案数据
	 * @param array $data	数据
	 */
	public function addAnswerOne($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_ANSWER)->insert($data, false, true);
	}
	
	/**
	 * 分页读取多条回答数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param string $order 排序
	 */
	public function getAnswerMorePage($where, $field='*', $offset=0, $psize=10, $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_ANSWER)->field($field)->limit($offset, $psize)
				->where($where)->order($order)->select();
	}
	
	/**
	 * 读取多条回答数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getAnswerMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_ANSWER)->field($field)
				->where($where)->order($order)->select();
	}
	
	/**
	 * 读取单条回答数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序字段
	 */
	public function getAnswerOne($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::V_ANSWER)->field($field)->where($where)->order($order)->find();
	}
}


