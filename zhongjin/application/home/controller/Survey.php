<?php
namespace app\home\controller;

use app\common\controller\Errcode;
use app\home\logic\SurveyLogic;
use app\home\logic\CustomerLogic;
use app\common\logic\FileLogic;

class Survey extends Root
{
	/**
	 * 增加
	 */
	public function add()
	{
		$param = $this->_getAddParam();
		$res = SurveyLogic::add($param);
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
		
		$info = SurveyLogic::editData($id);
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 编辑
	 */
	public function edit()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::INSERT_ERR, '更新失败');
		$param = $this->_getAddParam();
		
		$code = SurveyLogic::edit($id, $param);
		
		switch($code){
			case Errcode::SURVEY_UNSET:
				$msg = '调研不存在';
				break;
			case Errcode::SURVEY_DEL:
				$msg = '调研已删除';
				break;
			case Errcode::SURVEY_PUBLISH:
				$msg = '调研已发布';
				break;
			case Errcode::INSERT_ERR:
				$msg = '添加失败';
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
		
		$res = SurveyLogic::addQuestion($data);
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
		
		$info = SurveyLogic::editQuestionData($id);
		return $this->resultOk(['detail'=>$info]);
	}
	
	/**
	 * 编辑问题
	 */
	public function editQuestion()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		$data = $this->_getQuestionParam();

		$code = SurveyLogic::editQuestion($id, $data);
		
		switch($code){
			case Errcode::SURVEY_UNSET:
				$msg = '调研不存在';
				break;
			case Errcode::SURVEY_DEL:
				$msg = '调研已删除';
				break;
			case Errcode::SURVEY_PUBLISH:
				$msg = '调研已发布';
				break;
			case Errcode::INSERT_ERR:
				$msg = '添加失败';
				break;
		}
		
		isset($msg) && $this->result('', $code, $msg);
		return $this->resultOk();
	}
	
	/**
	 * 发布
	 */
	public function publish()
	{
		$id = input('param.id', 0, 'intval');
		$type = input('param.type', 0, 'intval');	// 0编辑并发布   1发布
		($id <=0 || !in_array($type, [0,1])) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		if(! $type){
			$data = $this->_getQuestionParam();
		
			$res = SurveyLogic::editQuestion($id, $data);
			$res === false && $this->result('', Errcode::INSERT_ERR, '添加问题失败');
		}
		
		$code = SurveyLogic::publish($id);
		switch($code){
			case Errcode::INSERT_ERR:
				$msg = '发布失败';
				break;
			case Errcode::SURVEY_UNSET:
				$msg = '调研不存在';
				break;
			case Errcode::SURVEY_QUE_UNSET:
				$msg = '请先添加问题';
				break;
			case Errcode::ARCHITECTRUE:
				$msg = '选择发送对象成员为空';
				break;
		}
		isset($msg) && $this->result('', $code, $msg);
		
		return $this->resultOk();
	}
	
	/**
	 * 列表
	 */
	public function lists()
	{
		$keywords = input('param.search', '', 'htmlspecialchars');
		$status = input('param.status', 0, 'intval');
		in_array($status, [0,1,2]) || $this->result('', Errcode::PARAM_ERR, '客户类别错误');
		
		$offset = input('param.offset', 0, 'intval');
		$psize = input('param.limit', 10, 'intval');
		
		$info = SurveyLogic::lists($keywords, $status, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1]]);
	}
	
	/**
	 * 删除
	 */
	public function delete()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$res = SurveyLogic::del($id);
		$res === Errcode::SURVEY_UNSET && $this->result('', Errcode::SURVEY_UNSET, '该调研不存在');
		$res === false && $this->result('', Errcode::UPDATE_ERR, '删除失败');
		
		return $this->resultOk();
	}
	
	/**
	 * 预览
	 */
	public function preview()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		SurveyLogic::preview($id);
		
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
		
		$info = SurveyLogic::answerAll($id, $offset, $psize);
		return $this->resultOk(['list'=>$info[0], 'total'=>$info[1], 'title'=>$info[2]]);
	}
	
	/**
	 * 查看一个人的回答详情
	 */
	public function answerDetailByMem()
	{
		$id = input('param.id', 0, 'intval');	// 回答记录ID
		$surveyId = input('param.survey_id', 0, 'intval');	// 调研ID
		$memid = input('param.memid', 0, 'intval');
		($id <=0 || $memid <=0 || $surveyId <=0) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$detail = SurveyLogic::answerDetailByMem($id);
		// 获取成员的名字和手机号码
		$memInfo = CustomerLogic::getInfoByMemids([$memid]);
		// 获取下一个ID和上一个ID
		$info = SurveyLogic::selectAnswer($id, $surveyId);
		$baseInfo = SurveyLogic::getBaseInfo($surveyId);
		
		return $this->resultOk([
			'detail'	=> $detail, 
			'title'		=> $baseInfo['title'],
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
		
		$info = SurveyLogic::statis($id);	
		return $this->resultOk(['info'=>$info[0], 'title'=>$info[1]]);
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
	 * 下载统计
	 */
	public function downloadStatis()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		SurveyLogic::downloadStais($id);
	}
	
	/**
	 * 下载报告
	 */
	public function downloadReport()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		SurveyLogic::downloadReport($id);
	}
	
	/**
	 * 下载报告
	 */
	public function downloadAnswer()
	{
		$id = input('param.id', 0, 'intval');	// 回答记录ID
		$memid = input('param.memid', 0, 'intval');
		$surveyId = input('param.survey_id', 0, 'intval');
		($id <=0 || $memid <=0) && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		SurveyLogic::downloadAnswer($id, $memid, $surveyId);
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
		
		$stime = input('start_time', 0, 'strtotime');
		$etime = input('end_time', 0, 'strtotime');
		if((!empty($stime)) && (!empty($etime))){
			$stime > $etime && $this->result('', Errcode::PARAM_ERR, '开始时间不能大于结束时间');
		}
		
		return [
			'base' => [
				'title'		=> $title,
				'imgid'		=> $imgid,
				'stime'		=> $stime,
				'etime'		=> $etime,
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
	 */
	private function _getQuestionParam()
	{
		$id = input('param.id', 0, 'intval');
		$id <=0 && $this->result('', Errcode::PARAM_ERR, '参数错误');
		
		$questions = input('param.questions/a');
		empty($questions) && $this->result('', Errcode::PARAM_ERR, '问题不能为空');
		
		$data = [];
		foreach($questions as $k =>$v){
			$type = intval($v['type']);
			! in_array($type, [1,2,3,4]) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'类型不能为空');
			
			if(isset($v['required'])){
				$isMust = intval($v['required']);
			}else{
				$isMust = 0;
			}
			
			$title = htmlspecialchars($v['title']);
			empty($title) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题标题不能为空');
			mb_strlen($title,'UTF-8')>32 && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题标题长度不能超过32个字符');
			
			switch($type){
				case 1: // 单选
				case 2: // 多选	
					$option = $v['option'];
					empty($option) && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题的选项不能为空');
					$_option = implode(',', $option);
					
					if($type == 2){
						$count = count($option);
						$least = intval($v['least']);
						$least >$count && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题的最少选择超过最大数');
						
						$most = intval($v['most']);
						$most >$count && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题的最多选择超过最大数');
						$most <$least && $this->result('', Errcode::PARAM_ERR, '第'.($k+1).'个问题的最多选择不能小于最小数');
						
						$extra = ['least'=>$least, 'most'=>$most];
						$extra = json_encode($extra);
					}
					break;
			}
			
			$data[$k] = [
				'survey_id'	=> $id,
				'type'			=> $type,
				'title'			=> $title,
				'is_must'		=> $isMust,
				'option'		=> '',
				'extra'			=> ''
			];
			isset($_option) && $data[$k]['option'] = $_option;
			isset($extra) && $data[$k]['extra'] = $extra;
			unset($_option);
			unset($extra);
			isset($v['question_id']) && (!empty($v['question_id'])) && $data[$k]['question_id'] = intval($v['question_id']);
		}
		
		return $data;
	}
}
