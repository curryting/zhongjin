<?php
namespace app\home\controller;

use app\common\controller\Errcode;
use app\home\logic\VoteLogic;
use app\home\logic\CustomerLogic;
use app\common\logic\FileLogic;

class Vote extends Root
{
	/**
	 * 增加
	 */
	public function add()
	{
		$param = $this->_getAddParam();
		$res = VoteLogic::add($param);
		$res === false && $this->result('', Errcode::INSERT_ERR, '增加失败');
	
		return $this->resultOk(['id'=>$res]);
	}
	
	/**
	 * 编辑数据
	 */
	public function editData()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = VoteLogic::editData($id);
		return $this->resultOk(['detail'=>$info]);
	}

	/**
	 * 编辑
	 */
	public function edit()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$param = $this->_getAddParam();
		
		$code = VoteLogic::edit($id, $param);
		
		switch($code){
			case Errcode::VOTE_UNSET:
				$msg = '投票不存在';
				break;
			case Errcode::VOTE_DEL:
				$msg = '投票已删除';
				break;
			case Errcode::VOTE_PUBLISH:
				$msg = '投票已发布';
				break;
			case Errcode::INSERT_ERR:
				$msg = '更新失败';
				break;
		}
		
		isset($msg) && $this->result('', $code, $msg);
		return $this->resultOk();
	}
	
	/**
	 * 保存问题
	 */
	public function save()
	{
		$data = $this->_getQuestionParam();
		
		$res = VoteLogic::addQuestion($data);
		$res === false && $this->result('', Errcode::INSERT_ERR, '添加问题失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 编辑问题数据
	 */
	public function editQuestionData()
	{
		$id = input('param.id', 0, 'intval');	// 调研ID
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = VoteLogic::editQuestionData($id);
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 编辑问题
	 */
	public function editQuestion()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$data = $this->_getQuestionParam(1);

		$code = VoteLogic::editQuestion($id, $data);
		
		switch($code){
			case Errcode::VOTE_UNSET:
				$msg = '投票不存在';
				break;
			case Errcode::VOTE_DEL:
				$msg = '投票已删除';
				break;
			case Errcode::VOTE_PUBLISH:
				$msg = '投票已发布';
				break;
			case Errcode::INSERT_ERR:
				$msg = '编辑失败';
				break;
		}
		
		isset($msg) && $this->result('', $code, $msg);
		return $this->resultOk();
	}
	
	/**
	 * 功能设置
	 */
	public function setup()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$param = $this->_getSetupParam();
		
		$code = VoteLogic::setup($id, $param);
		switch($code){
			case Errcode::VOTE_OVER:
				$msg = '该投票已结束，不能再更改';
				break;
			case Errcode::UPDATE_ERR:
				$msg = '更新失败';
				break;
		}
		isset($msg) && $this->result('', $code, $msg);
		
		return $this->resultOk();
	}
	
	/**
	 * 功能设置数据
	 */
	public function setupData()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = VoteLogic::setupData($id);
		return $this->resultOk(['info'=>$info]);
	}
	
	/**
	 * 发布
	 */
	public function publish()
	{
		$id = input('param.id', 0, 'intval');
		$type = input('param.etype', 0, 'intval');	// 0编辑并发布   1发布
		($id <=0 || !in_array($type, [0,1])) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		if(! $type){
			$param = $this->_getSetupParam();
		
			$res = VoteLogic::setup($id, $param);
			$res === false && $this->result('', Errcode::INSERT_ERR, '添加功能设置失败');
		}
		
		$code = VoteLogic::publish($id);
		switch($code){
			case Errcode::INSERT_ERR:
				$msg = '发布失败';
				break;
			case Errcode::VOTE_UNSET:
				$msg = '调研不存在';
				break;
			case Errcode::VOTE_QUE_UNSET:
				$msg = '请先添加问题';
				break;
			case Errcode::VOTE_SETUP_UNSET:
				$msg = '功能设置不能为空';
				break;
			case Errcode::ARCHITECTRUE:
				$msg = '选择发送对象成员为空';
				break;
		}
		isset($msg) && $this->result('', $code, $msg);
		
		return $this->resultOk();
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = VoteLogic::del($id);
		$res === Errcode::VOTE_UNSET && $this->result('', Errcode::VOTE_UNSET, '该投票不存在');
		$res === false && $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$status = input('param.status', 0, 'intval');
		in_array($status, [0,1,2,3]) || $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = VoteLogic::lists($keywords, $status, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 预览
	 */
	public function preview()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		VoteLogic::preview($id);
		
		return $this->resultOk();
	}
	
	/**
	 * 一个调研的所有回答内容
	 */
	public function answerAll()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = VoteLogic::answerAll($id, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
		
	/**
	 * 查看一个人的回答详情
	 */
	public function answerDetailByMem()
	{
		$id = input('param.id', 0, 'intval');	// 回答记录ID
		$voteId = input('param.vote_id', 0, 'intval');	// 投票ID
		$memid = input('param.memid', 0, 'intval');
		($id <=0 || $memid <=0 || $voteId <=0) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$detail = VoteLogic::answerDetailByMem($id);
		// 获取成员的名字和手机号码
		$memInfo = CustomerLogic::getInfoByMemids([$memid]);
		// 获取下一个ID和上一个ID
		$info = VoteLogic::selectAnswer($id, $voteId);
		
		return $this->resultOk([
			'detail'	=> $detail, 
			'name'		=> isset($memInfo[0]['name']) ? $memInfo[0]['name'] : '',
			'mobile'	=> isset($memInfo[0]['mobile']) ? $memInfo[0]['mobile'] : '',
			'preInfo'	=> $info['preInfo'],
			'nextInfo'	=> $info['nextInfo']
		]);
	}
	
	/**
	 * 统计
	 */
	public function statis()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$info = VoteLogic::statis($id);	
		return $this->resultOk($info);
	}
	
	/**
	 * 下载统计报表
	 */
	public function downloadStatis()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		VoteLogic::downloadStatis($id);	
		return ;
	}
	
	/**
	 * 上传图片
	 */
	public function upload()
	{
		try {
			$data = FileLogic::uploadOne('file', ['size'=>1024*1024*5, 'ext'=>'png,gif,jpg,jpeg']);
		} catch (\Exception $e) {
			return $this->resultErr($e->getMessage(), $e->getCode());
		}
		
		return $this->resultOk($data);
	}
	
	/**
	 * 获取增加时参数
	 * @return type
	 */
	private function _getAddParam()
	{
		$sendType = input('param.send_type', 0, 'intval');
		$sendObj = [];
		if($sendType == 1){
			// 发给指定成员
			$sendObj = input('param.send_obj/a');
			empty($sendObj) && $this->result('', Errcode::PARAM_ERR, '发送对象不能为空');
			$sendObj = json_encode($sendObj);
		}
		
		$title = input('param.title', '', 'htmlspecialchars');
		empty($title) && $this->result('', Errcode::PARAM_ERR, '标题不能为空');
		mb_strlen($title,'UTF-8') >32 && $this->result('', Errcode::PARAM_ERR, '标题不能超过32个字符');
		
		$imgid = input('param.cover_id', 0, 'intval');
		$imgid <=0 && $this->result('', Errcode::PARAM_ERR, '图片不能为空');
		
		$abstract = input('param.abstract', '', 'htmlspecialchars');
		mb_strlen($title,'UTF-8') >128 && $this->result('', Errcode::PARAM_ERR, '摘要不能超过128个字符');
		
		$content = input('param.description', '');
		$content = deal_xiumi_image($content);
		$content = htmlspecialchars($content);
		empty($content) && $this->result('', Errcode::PARAM_ERR, '描述不能为空');
		
		$finish = input('param.epilogue', '您的答案已提交，感谢您的参与', 'htmlspecialchars');

		return [
			'base' => [
				'title'		=> $title,
				'imgid'		=> $imgid
			],
			'extra' => [
				'abstract'	=> $abstract,
				'content'  => $content,
				'finish'	=> $finish,
				'to_contact' => $sendObj
			]
		];
	}
	
	/**
	 * 获取增加修改问题时参数
	 * @param $isEdit int 0:添加 1：编辑
	 */
	private function _getQuestionParam($isEdit=false)
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$type = input('param.type', 0, 'intval');
		! in_array($type, [1,2]) && $this->result('', Errcode::PARAM_ERR, '类型不能为空');
		
		$option = input('param.option/a', []);
		empty($option) && $this->result('', Errcode::PARAM_ERR, '问题的选项不能为空');
		$_option = implode(',', $option);
		
		$data = [
			'vote_id' => $id, 'type' => $type, 'option'=> $_option
		];
		
		if($type == 2){
			$count = count($option);
			$least = input('param.least', 0, 'intval');
			$least >$count && $this->result('', Errcode::PARAM_ERR, '问题的最少选择超过最大数');

			$most = input('param.most', 0, 'intval');
			$most >$count && $this->result('', Errcode::PARAM_ERR, '问题的最多选择超过最大数');
			$most <$least && $this->result('', Errcode::PARAM_ERR, '问题的最多选择不能小于最小数');

			$extra = ['least'=>$least, 'most'=>$most];
			$extra = json_encode($extra);
			
			$data['extra'] = $extra;
		}
		
		if($isEdit){
			$questionId = input('param.question_id', 0, 'intval');
			$questionId <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
			$data['question_id'] = $questionId;
		}

		return $data;
	}
	
	/**
	 * 获取功能设置参数
	 */
	private function _getSetupParam()
	{
		$status = input('param.status', 0, 'intval');
		$type = input('param.type', 1, 'intval'); // 1表示只能投 2 表示每日投
		(in_array($status, [1,2,3]) && in_array($type, [1,2])) || $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$data = ['status'=>$status, 'type'=>$type];

		$stime = input('start_time', 0, 'strtotime');
		$etime = input('end_time', 0, 'strtotime');
		if((!empty($stime)) && (!empty($etime))){
			$stime > $etime && $this->result('', Errcode::PARAM_ERR, '开始时间不能大于结束时间');
		}

		$num = input('param.num', 1, 'intval');
		($num >=1 && $num <= 255) || $this->result('', Errcode::PARAM_ERR, '次数只能在1-255之间');

		return [
			'status'=>$status, 'type'=>$type,
			'stime'=>$stime, 'etime'=>$etime,
			'num'=>$num
		];
	}
}
