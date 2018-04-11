<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\weixin\logic\SurveyLogic;

class Survey extends Root
{
	/**
	 * 列表
	 */
	public function lists()
	{
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = SurveyLogic::lists($offset, $psize, intval(MEMID));
		
		return $this->resultOk(['list'=>$info, 'isAdmin'=>intval(ADMINID)>0 ? 1 : 0]);
	}
	
	/**
	 * 详情
	 */
	public function detail()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		try{
			$info = SurveyLogic::detail($id);
		}catch(\Exception $e){
			$this->result('', $e->getCode(), $e->getMessage());
		}

		return $this->resultOk($info);
	}
	
	/**
	 * 已参与调研
	 */
	public function getJoinMem()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = SurveyLogic::getJoinInfo($id, 1, $offset, $psize);
		
		return $this->resultOk($info);
	}
	
	/**
	 * 未参与调研
	 */
	public function getUnjoinMem()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = SurveyLogic::getJoinInfo($id, 0, $offset, $psize);
		
		return $this->resultOk($info);
	}
	
	/**
	 * 回答
	 */
	public function answer()
	{
		intval(ADMINID) > 0 && $this->result('', Errcode::AMDIN_NOT_ALLOW, '管理员不能参与');
		$id = input('param.id', 0, 'intval'); // 调研ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$answer = input('param.answer/a', []);
		empty($answer) && $this->result('', Errcode::PARAM_ERR, '回答不能为空');
		
		// 验证回答是否合理 
		$questionInfo = SurveyLogic::questionDetail($id);
		$count = 1;
		foreach($questionInfo as $k => $v){
			if($v['required']){
				// 该问题必须回答
				(isset($answer[$k]) && !empty($answer[$k])) || $this->result('', Errcode::PARAM_ERR, '第'.$count.'个问题必须回答');
			}
			
			if($v['type'] == 2 && !empty($answer[$k])){
				$_count = count($answer[$k]);
				($_count >=$v['least'] && $_count <= $v['most']) || 
				$this->result('', Errcode::PARAM_ERR, '第'.$count.'个问题的答案必须在'.$v['least'].'-'.$v['most'].'之间');
			}
			$count++;
		}
		
		$res = SurveyLogic::answer($id, $answer, intval(MEMID), $questionInfo);
		$res === false && $this->result('', Errcode::INSERT_ERR, '调研失败');
		$res === Errcode::SURVEY_UNSET && $this->result('', Errcode::SURVEY_UNSET, '您没有权限参与该调研');
		$res === Errcode::SURVEY_ISSET_ANSWER && $this->result('', Errcode::SURVEY_ISSET_ANSWER, '您已参与过该调研');
		
		return $this->resultOk(['finish'=>$res]);
	}
	
	/**
	 * 一键提醒
	 */
	public function remind()
	{
		$id = input('param.id', 0, 'intval'); // 调研ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		SurveyLogic::remind($id);
		return $this->resultOk();
	}
	
}
