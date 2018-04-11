<?php
/**
 * 调研
 * by sherlock
 */
namespace app\common\model;

use think\Db;
use think\Model;
use think\Response;
use think\exception\HttpResponseException;

class Survey extends Model{
	const SURVEY = 'survey';
	const S_EXTRA = 'survey_info';
	const S_QUESTION = 'survey_question';
	const S_ANSWER = 'survey_answer';
	const S_ANSWER_CONTENT = 'survey_answer_content';
	const S_MEMBER = 'survey_member';
	
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
		
		return $this->table(self::SURVEY)->field($field)->where($where)->find();
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
		
		return $this->table(self::SURVEY)->field($field)
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
		
		return $this->table(self::SURVEY)->field($field)->limit($offset, $psize)
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
		
		return $this->table(self::SURVEY)->where($where)->count();
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
		
		return $this->table(self::SURVEY)->insert($data, false, true);
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
		
		return $this->table(self::SURVEY)->where($where)->update($data);
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
		
		return $this->table(self::S_EXTRA)->field($field)->where($where)->find();
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
		
		return $this->table(self::S_EXTRA)->field($field)->limit($offset, $psize)
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
		
		return $this->table(self::S_EXTRA)->insert($data, false, true);
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
		
		return $this->table(self::S_EXTRA)->where($where)->update($data);
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
		
		return $this->table(self::S_QUESTION)->insertAll($data);
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
		
		return $this->table(self::S_QUESTION)->insert($data, false, true);
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
		
		return $this->table(self::S_QUESTION)->where($where)->update($data);
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
		
		return $this->table(self::S_QUESTION)->field($field)->where($where)->find();
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
		
		return $this->table(self::S_QUESTION)->field($field)->where($where)
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
		
		return $this->table(self::S_MEMBER)->insertAll($data);
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
		
		return $this->table(self::S_MEMBER)->where($where)->update($data);
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
		
		return $this->table(self::S_MEMBER)->field($field)->limit($offset, $psize)
				->where($where)->order($order)->select();
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
		
		return $this->table(self::S_MEMBER)->field($field)
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
		
		return $this->table(self::S_MEMBER)->field($field)
				->where($where)->order($order)->find();
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
		
		return $this->table(self::S_MEMBER)->where($where)->count();
	}
	
	/**
	 * 统计调研有多少人回答
	 * @param array $surveyIds 调研IDs
	 */
	public function countMemberNum($surveyIds)
	{
		if(empty($surveyIds)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		$sql = 'SELECT count(*) AS `count`,`survey_id` FROM ' .self::S_MEMBER. 
			' WHERE `survey_id` in(' .implode(',',$surveyIds). ') AND `is_answer`=1 AND `is_del`=0 GROUP BY `survey_id`;';
		return Db::query($sql);
	}

	/**
	 * 读取多条回答数据
	 * @param array  $where 条件
	 * @param string $field 需要读取的字段
	 * @param string $order 排序
	 */
	public function getAnswerContentMore($where, $field='*', $order='')
	{
		if(empty($where)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::S_ANSWER_CONTENT)->field($field)
				->where($where)->order($order)->select();
	}
	
	/**
	 * 增加多条答案具体内容数据
	 * @param array $data	数据
	 */
	public function addAnswerContentMore($data)
	{
		if(empty($data)){
			$response = Response::create($this->errArray, 'json')->header([]);
			throw new HttpResponseException($response);
		}
		
		return $this->table(self::S_ANSWER_CONTENT)->insertAll($data);
	}
}


