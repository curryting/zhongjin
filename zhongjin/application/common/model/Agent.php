<?php
/**
 * 应用
 * by sherlock
 */
namespace app\common\model;

use think\Model;

class Agent extends Model{
	
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
}


