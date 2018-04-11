<?php
/**
 * 调研操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use think\Db;
use app\common\model\Survey;
use app\common\logic\FileLogic;
use app\common\logic\UserLogic;
use app\common\logic\AgentLogic;
use app\common\controller\Errcode;
use app\common\logic\ExeclLogic;

define('APP_ID', 3);

class SurveyLogic{
	
	/**
	 * 添加
	 * @param array $param	参数
	 */
	public static function add(&$param)
	{
		$model = new Survey();
		//开启事务
		Db::startTrans();
		try {
			$id = $model->addBaseOne(array_merge($param['base'], [
				'create_time'=>NOW_TIME, 'userid'=>intval(session('user'))
			]));
			$model->addExtraOne(array_merge($param['extra'], ['survey_id'=>$id]));
			
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		
		return $id;
	}
	
	/**
	 * 编辑数据
	 * @param int	$id		调研ID
	 */
	public static function editData($id)
	{
		$model = new Survey();
		// 读取基本数据
		$baseInfo = $model->getBaseOne(['id'=>$id,'is_del'=>0], 'title,imgid,stime,etime');
		// 读取图片地址
		$imgInfo = FileLogic::getOne($baseInfo['imgid'], false, true);
		// 读取扩展信息
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'abstract,content,to_contact,finish');
		
		$data = [
			'cover' => $imgInfo['http_path'],
			'id' => $id, 'cover_id' => $baseInfo['imgid'], 
			'title'		=> htmlspecialchars_decode($baseInfo['title']),
			'abstract'	=> htmlspecialchars_decode($extraInfo['abstract']),
			'epilogue' => htmlspecialchars_decode($extraInfo['finish']),
			'description' => htmlspecialchars_decode($extraInfo['content'])
		];
		if(empty($extraInfo['to_contact'])){
			$data['send_type'] = 0;
		}else{
			$data['send_type'] = 1;
			$data['send_obj'] = json_decode($extraInfo['to_contact'], true);
			$data['names'] = ArchitectureLogic::getName($data['send_obj']);
		}
		
		$baseInfo['stime'] !=0 && $data['start_time'] = date('Y-m-d H:i:s', $baseInfo['stime']);
		$baseInfo['etime'] !=0 && $data['end_time'] = date('Y-m-d H:i:s', $baseInfo['etime']);
		return $data;
	}

	/**
	 * 编辑
	 * @param int	$id		调研ID
	 * @param array $param	参数
	 */
	public static function edit($id, &$param)
	{
		$model = new Survey();
	
		$info = $model->getBaseOne(['id'=>$id], 'is_del,is_publish');
		if(empty($info)) return Errcode::SURVEY_UNSET;
		if($info['is_del'] == 1) return Errcode::SURVEY_DEL;
		if($info['is_publish'] == 1) return Errcode::SURVEY_PUBLISH;
		
		//开启事务
		Db::startTrans();
		try{
			$model->updateBase(['id'=>$id], array_merge($param['base']), [
				'update_time'=>NOW_TIME, 'userid'=>intval(session('user'))
			]);
			$model->updateExtra(['survey_id'=>$id], $param['extra']);
			
			Db::commit();
		}catch (\Exception $e){
			Db::rollback();
			return Errcode::INSERT_ERR;
		}
		
		return Errcode::SUCCESS;
	}
	
	/**
	 * 添加问题
	 * @param array $data	数据
	 */
	public static function addQuestion(&$data)
	{
		$model = new Survey();
		$res = $model->addQuestionMore($data);
		if(empty($res)) return false;
		
		return true;
	}
	
	/**
	 * 编辑问题数据
	 * @param int	$id		调研ID
	 */
	public static function editQuestionData($id)
	{
		$model = new Survey();
		// 读取问题
		$questionInfo = $model->getQuestionMore(['survey_id'=>$id, 'is_del'=>0], 'id,type,title,is_must,option,extra');
		
		$data = [];
		foreach($questionInfo as $k =>$v){
			$data[$k]['question_id']			= $v['id'];
			$data[$k]['type']		= $v['type'];
			$data[$k]['title']		= htmlspecialchars_decode($v['title']);
			$data[$k]['required']	= $v['is_must'];
			if(empty($v['option'])){
				$data[$k]['option']	 = [];
			}else{
				$data[$k]['option']		= explode(',', $v['option']);
			}
			
			if($v['type'] == 2){
				// 多选
				$extra = json_decode($v['extra'], true);
				$data[$k]['most'] = $extra['most'];
				$data[$k]['least'] = $extra['least'];
			}
		}
		
		return $data;
	}
	
	/**
	 * 编辑多条问题
	 * @param int	$id		调研ID
	 * @param array $data	数据
	 */
	public static function editQuestion($id, &$data)
	{
		$model = new Survey();
		
		$info = $model->getBaseOne(['id'=>$id], 'is_del,is_publish');
		if(empty($info)) return Errcode::SURVEY_UNSET;
		if($info['is_del'] == 1) return Errcode::SURVEY_DEL;
		if($info['is_publish'] == 1) return Errcode::SURVEY_PUBLISH;
		
		// 找到所有的问题ID
		$questionInfo = $model->getQuestionMore(['survey_id'=>$id, 'is_del'=>0], 'id');
		$questionIds = [];
		foreach($questionInfo as $v){
			$questionIds[] = $v['id'];
		}
		
		// 找出没有删除的问题
		$unDelIds = [];
		foreach($data as $v){
			isset($v['question_id']) && $unDelIds[] = $v['question_id'];
		}
		
		// 找出需要删除的调研问题
		$delIds = array_values(array_diff($questionIds, $unDelIds));
		
		//开启事务
		Db::startTrans();
		try{
			// 删除已经删除的问题
			empty($delIds) || $model->updateQuestion(['id'=>['in', $delIds]], ['is_del'=>1]);
			foreach($data as $k => $v){
				if(isset($v['question_id'])){
					$questionId = $v['question_id'];
					unset($v['question_id']);
					$model->updateQuestion(['id'=>$questionId], $v);
				}else{
					$model->addQuestionOne($v);
				}
			}
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			return Errcode::INSERT_ERR;
		}
		
		return Errcode::SUCCESS;
	}
	
	/**
	 * 发布调研
	 * @param int	$id		调研ID
	 */
	public static function publish($id)
	{
		$model = new Survey();
		// 读取问题是否为空
		$questionInfo = $model->getQuestionOne(['survey_id'=>$id], 'id');
		if(empty($questionInfo)) return Errcode::SURVEY_QUE_UNSET; 
		
		// 读取发送对象
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'to_contact,abstract');
		if(empty($extraInfo))	return Errcode::SURVEY_UNSET;
		
		// 解析发送对象
		$memids = [];
		$sendObj = json_decode($extraInfo['to_contact'], true);
		if(empty($sendObj)){
			// 发给全体客户
			$info = CustomerLogic::getName();
			foreach($info as $v){
				$memids[] = $v['id'];
			}
			unset($info);
		}else{
			// 发给指定成员
			$memids = ArchitectureLogic::parseToMember($sendObj);
		}
		if(empty($memids)) return Errcode::ARCHITECTRUE;
		
		//开启事务
		Db::startTrans();
		try{
			$data = [];
			foreach($memids as $v){
				$data[] = [
					'survey_id'	=> $id,
					'memid'			=> $v
				];
			}
			$model->addMemberMore($data);
			$model->updateBase(['id'=>$id], ['is_publish'=>1]);
			Db::commit();
		}catch(\Exception $e){
			return Errcode::INSERT_ERR;
			Db::rollback();
		}
		
		// 发送微信消息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,imgid');
		// 读取图片地址
		$fileInfo = FileLogic::getOne($baseInfo['imgid'], false, true, false);
		
		$aid = AgentLogic::getAidByType('survey');
		$sendMsg[] = [
			'title'		=> $baseInfo['title'],
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/survey/'.$id.'/detail/aid/'.$aid,
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path'],
			'desc'		=> mb_substr($extraInfo['abstract'], 0, 50)
		];
		
		WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'survey');
		
		return Errcode::SUCCESS;
	}
	
	/**
	 * 列表
	 * @param string $keywords	关键字
	 * @param int $status		0:读全部1读正常2读结束
	 * @param int $offset		偏移量
	 * @param int $psize		一页大小
	 */
	public static function lists($keywords='', $status=0, $offset=0, $psize=10)
	{
		$where = ['is_del'=>0];
		empty($keywords) || $where['title'] = ['like', "%$keywords%"];
		in_array($status, [1,2]) && $where['status'] = $status;
		
		$model = new Survey();
		$count = $model->countBase($where);
		$list = $model->getBaseMorePage($where, 'id,userid,title,stime,etime,status,is_publish', 
			$offset, $psize, 'id DESC');
		
		$userids = $ids = [];
		foreach($list as $v){
			$ids[] = $v['id'];
			$userids[] = $v['userid'];
		}
		$userids = array_values(array_filter(array_unique($userids)));
		if(! empty($userids)){
			$userInfo = UserLogic::getNameByUserids($userids);
		}
		
		// 读取答卷数量
		$ids = array_values(array_filter(array_unique($ids)));
		if(! empty($ids)){
			$_countInfo = $model->countMemberNum($ids);
			foreach($_countInfo as $v){
				$countInfo[$v['survey_id']] = $v['count'];
			}
		}
		
		foreach($list as &$v){
			$v['title']			= htmlspecialchars_decode($v['title']);
			$v['paper_num']	= isset($countInfo[$v['id']]) ? $countInfo[$v['id']] : 0;
			$v['author']		= isset($userInfo[$v['userid']]) ? $userInfo[$v['userid']] : '';
			$v['start_time']	= $v['stime'] == 0 ? '-' : date('Y-m-d H:i:s', $v['stime']);
			$v['end_time']		= $v['etime'] == 0 ? '-' : date('Y-m-d H:i:s', $v['etime']);
			unset($v['stime']);
			unset($v['etime']);
			unset($v['userid']);
		}
		
		return [empty($list) ? [] : $list, $count];
	}
	
	/**
	 * 删除
	 * @param int	$id		调研ID
	 */
	public static function del($id)
	{
		$model = new Survey();
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'is_publish,is_del');
		if(empty($baseInfo)) return Errcode::SURVEY_UNSET;
		
		if($baseInfo['is_publish']){
			$model->updateMember(['survey_id'=>$id], ['is_del'=>1]);
		}
		$res = $model->updateBase(['id'=>$id], ['is_del'=>1]);
		
		return $res>0 ? true : false;
	}
	
	/**
	 * 预览
	 * @param int	$id		调研ID
	 */
	public static function preview($id)
	{
		$model = new Survey();
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'userid,imgid,title');
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'abstract');		
		// 查找图片地址
		$fileInfo = FileLogic::getOne($baseInfo['imgid'], false, true);
		$aid = AgentLogic::getAidByType('survey');
		
		$sendMsg[] = [
			'title'		=> htmlspecialchars_decode($baseInfo['title']),
			'desc'		=> mb_substr(htmlspecialchars_decode($extraInfo['abstract']), 0, 50, 'UTF-8'),
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/survey/'.$id.'/detail/aid/'.$aid.'/admin',
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path']
		];
		// 发送微信消息
		WxLogic::sendNewsMsgByAdmin($sendMsg, [$baseInfo['userid']], 'survey');
		return true;
	}
	
	/**
	 * 一个调研的所有回答
	 * @param int $id		调研ID
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 */
	public static function answerAll($id, $offset=0, $psize=10)
	{
		$model = new Survey();
		
		$count = $model->countMember(['survey_id'=>$id, 'is_answer'=>1, 'is_del'=>0]);
		if($offset >=0){
			$list = $model->getMemberMorePage(['survey_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 
				'id,update_time,memid', $offset, $psize, 'update_time DESC');
		}else{
			$list = $model->getMemberMore(['survey_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 
				'id,update_time,memid', 'update_time DESC');
		}
		
		$memids = $ids = [];
		foreach($list as $v){
			$memids[] = $v['memid'];
		}
		
		$memids = array_values(array_filter(array_unique($memids)));
		if(! empty($memids)){
			$_info = CustomerLogic::getInfoByMemids($memids);
			foreach($_info as $v){
				$memInfo[$v['id']] = [
					'name'		=> $v['name'],
					'mobile'	=> $v['mobile']
				];
			}
			unset($_info);
		}
		
		foreach($list as &$v){
			$v['time']		= date('Y-m-d H:i:s', $v['update_time']);
			$v['name']		= isset($memInfo[$v['memid']]) ? $memInfo[$v['memid']]['name'] : '';
			$v['mobile']	= isset($memInfo[$v['memid']]) ? $memInfo[$v['memid']]['mobile'] : '';
			unset($v['create_time']);
		}
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title');
		return [empty($list) ? [] : $list, $count, $baseInfo['title']];
	}
	
	/** 
	 * 一个人的回答
	 * @param int $id		回答记录ID
	 */
	public static function answerDetailByMem($id)
	{
		$model = new Survey();
		
		$_list = $model->getAnswerContentMore(['answer_id'=>$id, 'is_del'=>0], 'question_id,answer');
		
		$questionIds = $answerInfo = [];
		foreach($_list as $v){
			$questionIds[] = $v['question_id'];
			$answerInfo[$v['question_id']] = $v['answer'];
		}
		unset($_list);
		
		// 查询问题的题目名称即附加信息
		$questionIds = array_values(array_filter(array_unique($questionIds)));
		if(! empty($questionIds)){
			$questionInfo = $model->getQuestionMore(['id'=> ['in', $questionIds]], 
				'id,type,title,option');
			foreach($questionInfo as &$v){
				$v['title']		= htmlspecialchars_decode($v['title']);
				$v['answer']	= '';
				$type = intval($v['type']);
				switch($type){
					case 1: // 单选
						$v['option'] = explode(',', $v['option']);
						$v['answer'] = isset($answerInfo[$v['id']]) ? $answerInfo[$v['id']] : '';
						break;
					case 2: // 多选
						$v['option'] = explode(',', $v['option']);
						$v['answer'] = isset($answerInfo[$v['id']]) ? json_decode($answerInfo[$v['id']], true) : '';
						break;
					case 3: // 单文本
					case 4: // 多文本
						$v['answer'] = isset($answerInfo[$v['id']]) ? $answerInfo[$v['id']] : '';
						break;
				}
			}
		}
		
		return isset($questionInfo) ? $questionInfo : [];
	}
	
	/**
	 * 查找这条回答的上一个ID和下一个ID
	 * @param int $id		回答记录ID
	 * @param int $surveyId 调研ID
	 */
	public static function selectAnswer($id, $surveyId)
	{
		$model = new Survey();
		
		// 读取此时这条回答的回答时间
		$answerInfo = $model->getMemberOne(['id'=>$id], 'update_time');
		
		$where = ['survey_id'=>$surveyId, 'is_answer'=>1, 'is_del'=>0];
		// 读取下一条ID
		$info = $model->getMemberOne(array_merge($where, ['update_time'=>['gt', $answerInfo['update_time']]]), 'id,memid', 'update_time ASC');
		if(isset($info['id']) && ! empty($info['id'])){
			$preInfo = [
				'id' => $info['id'],
				'memid' => $info['memid']
			];
		}
		
		// 读取上一条ID
		$info = $model->getMemberOne(array_merge($where,['update_time'=>['lt', $answerInfo['update_time']]]), 'id,memid', 'update_time DESC');
		if(isset($info['id']) && ! empty($info['id'])){
			$nextInfo = [
				'id' => $info['id'],
				'memid' => $info['memid']
			];
		}
		
		return [
			'nextInfo' => isset($nextInfo) ? $nextInfo : [], 
			'preInfo'  => isset($preInfo) ? $preInfo : []
		];
	}
	
	/**
	 * 获取标题基本信息
	 * @param int $id 调研ID
	 */
	public static function getBaseInfo($id)
	{
		$model = new Survey();
		
		return $model->getBaseOne(['id'=>$id], 'title');
	}
	
	/**
	 * 统计
	 * @param int $id	调研ID
	 */
	public static function statis($id)
	{
		$model = new Survey();
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title');
		
		// 读取所有问题
		$questionInfo = $model->getQuestionMore(['survey_id'=>$id, 'is_del'=>0],
			'id,type,title,option');
		$questionIds = [];
		foreach($questionInfo as $v){
			$questionIds[] = $v['id'];
		}
		
		// 读取一个调研里面所有问题的所有答案
		$questionIds = array_values(array_filter(array_unique($questionIds)));
		if(! empty($questionIds)){
			$_answerInfo = $model->getAnswerContentMore(['question_id'=>['in', $questionIds]], 'question_id,answer');
			$answerInfo = [];
			foreach($_answerInfo as $v){
				$answerInfo[$v['question_id']][] = $v['answer'];
			}
			unset($_answerInfo);
			
			// 统计每个问题有多少人回答
			$statis = [];
			if(! empty($answerInfo)){
				foreach($questionInfo as $v){
					$questionId = intval($v['id']);
					$type = intval($v['type']);
					$statis[$questionId] = ['type' => $type, 'count'=>0, 'title'=>$v['title']];

					// 一个问题的所有回答
					$_answerInfo = $answerInfo[$questionId];
					$statis[$questionId]['count'] = count($_answerInfo);

					switch($type){
						case 1:
						case 2:
							$option = explode(',', $v['option']);
							foreach($option as $k => $v){
								// 单选或者多选的一个选项的回答票数
								$statis[$questionId]['answer'][$k]['count'] = 0;
								$statis[$questionId]['answer'][$k]['title'] = $v;
							}

							foreach($_answerInfo as $v1){
								if($type == 1){
									$statis[$questionId]['answer'][$v1-1]['count']++;
								}else{
									$__answer = json_decode($v1, true);
									foreach($__answer as $v2){
										// 答案比问题选项值大1
										$statis[$questionId]['answer'][$v2-1]['count']++;
									}
								}
							}

							// 统计回答占比
							foreach($statis[$questionId]['answer'] as $k1 => &$v1){
								$v1['rate'] = round(floatval($v1['count']*100/$statis[$questionId]['count']), 1).'%';
							}
							unset($v1);
							unset($statis[$questionId]['count']);
							break;
					}
				}
			}
		}
		
		return [isset($statis) ? array_values($statis) : [], $baseInfo['title']];
	}
	
	/**
	 * 定时任务更新状态
	 */
	public static function timerUpdate()
	{
		$model = new Survey();
		
		$list = $model->getBaseMore(['is_publish'=>1, 'status'=>1], 'id,etime');
		$ids =[];
		foreach($list as $v){
			($v['etime'] !=0 && NOW_TIME>$v['etime']) && $ids[] = $v['id'];
		}
		
		empty($ids) || $model->updateBase(['id'=> ['in', $ids]], ['status'=>2]);
	}
	
	/**
	 * 下载统计
	 * @param int $id	调研ID
	 */
	public static function downloadStais($id)
	{
		list($data, $title) = self::statis($id);
		
		$time = date('YmdHis', NOW_TIME);
		$title = '调研-'.$time;
	
		$tableHead = $statis = [];
		$i = 1;
		foreach($data as $v){
			switch($v['type']){
				case 1:
				case 2:
					$tableHead[] = [
						['title' => '选项', 'width' => 35],
						['title' => '票数', 'width' => 15],
						['title' => '占比', 'width' => 15]
					];
					$key = "第{$i}题:{$v['title']} " .($v['type'] ==1? '【单选】' : '【多选】');
					foreach($v['answer'] as $v1){
						$statis[$key][] = [
							'title' => $v1['title'],
							'count' => $v1['count'],
							'rate'  => $v1['rate'] 
						];
					}
					break;
				case 3:
				case 4:
					$tableHead[] = [
						['title' => '参与人数', 'width' => 35],
						['title' => $v['count']]
					];
					$key = "第{$i}题:{$v['title']} 【简答】";
					//$statis[$key][] = ['count'=>$v['count']];
					$statis[$key][] = [];
					break;
			}
			$i++;
		}
		unset($data);
		ExeclLogic::download($title, $tableHead, $statis);
	}
	
	/**
	 * 下载报告
	 * @param int $id	调研ID
	 */
	public static function downloadReport($id)
	{
		list($data, $count, $title) = self::answerAll($id, -1);
		
		$tableHead = [
			[
				['title' => '提交答卷时间', 'width' => 20],
				['title' => '用户姓名', 'width' => 10],
				['title' => '手机号码', 'width' => 15]
			]
		];
		$time = date('YmdHis', NOW_TIME);
		$filename = '投票报告-'.$time;
		
		$record = [];
		foreach($data as $v){
			$record[$title][] = [
				'time' => $v['time'],
				'name' => $v['name'],
				'mobile' => $v['mobile']
			];
		}
		
		ExeclLogic::download($filename, $tableHead, $record);
	}
	
	/**
	 * 下载答案
	 * @param int $id	回答记录ID
	 * @param int $memid 客户ID
	 * @param int $surveyId 调研ID
	 */
	public static function downloadAnswer($id, $memid, $surveyId)
	{
		$data = self::answerDetailByMem($id);
		$baseInfo = self::getBaseInfo($surveyId);
		// 获取成员的名字和手机号码
		$memInfo = CustomerLogic::getInfoByMemids([$memid]);
		
		$tableHead = $answer = [];
		$time = date('YmdHis', NOW_TIME);
		$filename = '投票答案-'.$time;
		
		vendor('phpexcel.PHPExcel');
		$excel = new \PHPExcel();
		$excel->getActiveSheet()->setCellValue('A1', $baseInfo['title']);
		$excel->getActiveSheet()->setCellValue('A2', '用户名:'.$memInfo[0]['name']);
		$excel->getActiveSheet()->setCellValue('B2', '手机号:'.$memInfo[0]['mobile']);
		$excel->getActiveSheet()->setCellValue('A3', '投票答案');
		$excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
		$excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
		
		$i = 1;
		$j = 4;
		foreach($data as $v){
			switch($v['type']){
				case 1:
					$key = "第{$i}题:{$v['title']} 【单选】";
					$ascii = 65+$v['answer']-1;
					$value = '选项'.chr($ascii).':'.$v['option'][$v['answer']-1];
					break;
				case 2:
					$key = "第{$i}题:{$v['title']} 【多选】";
					$value = '';
					foreach($v['answer'] as $v1){
						$ascii = 65+$v1-1;
						$tmp = '选项'.chr($ascii).':'.$v['option'][$v1-1];
						$value .= $tmp; 
					}
					break;
				case 3:
				case 4:
					$key = "第{$i}题:{$v['title']} 【简答】";
					$value = $v['answer'];
					break;
			}
			$excel->getActiveSheet()->setCellValue('A'.$j, $key);
			$excel->getActiveSheet()->setCellValue('B'.$j, $value);
			$i++;
			$j++;
		}
		
		$write = new \PHPExcel_Writer_Excel5($excel);
		$filename = urlencode($filename);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl;charset=utf-8");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header("Content-Disposition:attachment;filename={$filename}.xls");//要生成的表名
		header("Content-Transfer-Encoding:binary");
		$write->save('php://output');
		exit;
	}
}
