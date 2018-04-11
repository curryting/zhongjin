<?php
namespace app\weixin\controller;

use app\common\controller\Errcode;
use app\weixin\logic\VoteLogic;

class Vote extends Root
{
	/**
	 * 列表
	 */
	public function lists()
	{
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = VoteLogic::lists($offset, $psize, intval(MEMID));
		
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
			$info = VoteLogic::detail($id);
		}catch(\Exception $e){
			$this->result('', $e->getCode(), $e->getMessage());
		}

		return $this->resultOk($info);
	}
	
	/**
	 * 统计
	 */
	public function statis()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$data = VoteLogic::statis($id);
		return $this->resultOk(['answer'=>$data[0], 'type'=>$data[1]]);
	}
	
	/**
	 * 回答
	 */
	public function answer()
	{
		$id = input('param.id', 0, 'intval'); // 投票ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$answer = input('param.answer', '', 'strval');
		empty($answer) && $this->result('', Errcode::PARAM_ERR, '回答不能为空');
		
		// 验证回答是否合理 
		$questionInfo = VoteLogic::questionDetail($id);

		if($questionInfo['type'] == 2){
			$_answer = explode(',', $answer);
			$count = count($_answer);
			$questionInfo['extra'] = json_decode($questionInfo['extra'], true);
			$least = $questionInfo['extra']['least'];
			$most = $questionInfo['extra']['most'];
			($count >=$least && $count <= $most) ||
				$this->result('', Errcode::PARAM_ERR, '问题的答案必须在'.$least.'-'.$most.'之间');
		}

		$res = VoteLogic::answer($id, $answer, intval(MEMID), $questionInfo);
		switch($res){
			case Errcode::VOTE_NO_BEGINNING:
				$msg = '投票未开始';
				break;
			case Errcode::VOTE_OVER:
				$msg = '投票已结束';
				break;
			case Errcode::VOTE_UPPER_LIMIT:
				$msg = '投票次数已达上限';
				break;
			case Errcode::INSERT_ERR:
				$msg = '投票失败';
				break;
			case Errcode::VOTE_SUSPEND:
				$msg = '投票已暂停';
				break;
			case Errcode::AUTH_ERR:
				$msg = '没有权限投票';
				break;
			case Errcode::VOTE_PUBLISH:
				$msg = '该投票没发布';
				break;
			case Errcode::NO_AUTH:
				$msg = '没有权限操作';
				break;
		}
		isset($msg) && $this->result('', $res, $msg);
		
		return $this->resultOk(['finish'=>$res]);
	}
	
	/**
	 * 一键提醒
	 */
	public function remind()
	{
		$id = input('param.id', 0, 'intval'); // 调研ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		VoteLogic::remind($id);
		return $this->resultOk();
	}
	
	/**
	 * 已参与投票
	 */
	public function getJoinMem()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = VoteLogic::getJoinInfo($id, 1, $offset, $psize);
		// 读取这些客户的选项
		$answer = VoteLogic::answerByIds($id, $info['ids']);
		foreach($info['mem_info'] as &$v){
			isset($answer[$v['id']]) && $v['answer'] = $answer[$v['id']];
			unset($answer[$v['id']]);
		}
		unset($answer);
			
		return $this->resultOk(['mem_info'=>$info['mem_info']]);
	}
	
	/**
	 * 未参与投票
	 */
	public function getUnjoinMem()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = VoteLogic::getJoinInfo($id, 0, $offset, $psize);
		
		return $this->resultOk(['mem_info'=>$info['mem_info']]);
	}
}
