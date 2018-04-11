<?php
/**
 * 投票操作逻辑层
 * by sherlock
 */

namespace app\home\logic;

use think\Db;
use app\common\model\Vote;
use app\common\logic\FileLogic;
use app\common\logic\UserLogic;
use app\common\logic\AgentLogic;
use app\common\controller\Errcode;
use app\common\logic\ExeclLogic;

define('APP_ID', 4);

class VoteLogic{
	
	/**
	 * 添加
	 * @param array $param	参数
	 */
	public static function add(&$param)
	{
		$model = new Vote();
		//开启事务
		Db::startTrans();
		try {
			$id = $model->addBaseOne(array_merge($param['base'], [
				'create_time'=>NOW_TIME, 'userid'=>intval(session('user'))
			]));
			$model->addExtraOne(array_merge($param['extra'], ['vote_id'=>$id]));
			
			Db::commit();
		} catch (\Exception $e) {
			Db::rollback();
			return false;
		}
		
		return $id;
	}
	
	/**
	 * 编辑数据
	 * @param int	$id		投票ID
	 */
	public static function editData($id)
	{
		$model = new Vote();
		// 读取基本数据
		$baseInfo = $model->getBaseOne(['id'=>$id,'is_del'=>0], 'title,imgid');
		// 读取图片地址
		$imgInfo = FileLogic::getOne($baseInfo['imgid'], false, true);
		// 读取扩展信息
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'abstract,content,to_contact,finish');
		
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
		
		return $data;
	}
	
	/**
	 * 编辑
	 * @param int	$id		投票ID
	 * @param array $param	参数
	 */
	public static function edit($id, &$param)
	{
		$model = new Vote();
	
		$info = $model->getBaseOne(['id'=>$id], 'is_del,is_publish');
		if(empty($info)) return Errcode::VOTE_UNSET;
		if($info['is_del'] == 1) return Errcode::VOTE_DEL;
		if($info['is_publish'] == 1) return Errcode::VOTE_PUBLISH;
		
		//开启事务
		Db::startTrans();
		try{
			$model->updateBase(['id'=>$id], array_merge($param['base']), [
				'update_time'=>NOW_TIME, 'userid'=>intval(session('user'))
			]);
			$model->updateExtra(['vote_id'=>$id], $param['extra']);
			
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
	public static function addQuestion($data)
	{
		$model = new Vote();
		$id = $model->addQuestionOne($data);
		
		return $id >0 ? true : false;
	}
	
	/**
	 * 编辑问题数据
	 * @param int	$id		投票ID
	 */
	public static function editQuestionData($id)
	{
		$model = new Vote();
		// 读取问题
		$questionInfo = $model->getQuestionOne(['vote_id'=>$id, 'is_del'=>0], 'id,type,option,extra');
		if(empty($questionInfo)) return [];
		
		$data = [
			'question_id' => $questionInfo['id'],
			'type' => $questionInfo['type']
		];
		
		if(empty($questionInfo['option'])){
			$data['option'] = [];
		}else{
			$data['option'] = explode(',', $questionInfo['option']);
		}
		
		if($questionInfo['type'] == 2){
			// 多选
			$extra = json_decode($questionInfo['extra'], true);
			$data['most'] = $extra['most'];
			$data['least'] = $extra['least'];
		}
		
		return $data;
	}
	
	/**
	 * 编辑多条问题
	 * @param int	$id		投票ID
	 * @param array $data	数据
	 */
	public static function editQuestion($id, $data)
	{
		$model = new Vote();
		
		$info = $model->getBaseOne(['id'=>$id], 'is_del,is_publish');
		if(empty($info)) return Errcode::VOTE_UNSET;
		if($info['is_del'] == 1) return Errcode::VOTE_DEL;
		if($info['is_publish'] == 1) return Errcode::VOTE_PUBLISH;
		
		$questionId = $data['question_id'];
		unset($data['question_id']);
		$model->updateQuestion(['id'=>$questionId], $data);
		
		return Errcode::SUCCESS;
	}
	
	/**
	 * 功能设置
	 * @param int	$id		投票ID
	 * @param array $data	数据
	 */
	public static function setup($id, $data)
	{
		$model = new Vote();
		$baseInfo = $model->getBaseOne(['id'=>$id], 'etime');
		if($baseInfo['etime'] !=0 && NOW_TIME > $baseInfo['etime']){
			return Errcode::VOTE_OVER;
		}
		$res = $model->updateBase(['id'=>$id], array_merge($data, ['update_time'=>NOW_TIME]));
		
		return $res > 0 ? Errcode::SUCCESS : Errcode::UPDATE_ERR;
	}

	/**
	 * 功能设置初始化数据
	 * @param int	$id		投票ID
	 */
	public static function setupData($id)
	{
		$model = new Vote();
		// 读取基本数据
		$baseInfo = $model->getBaseOne(['id'=>$id,'is_del'=>0], 'status,stime,etime,type,num,is_publish');
		if(empty($baseInfo)) return [];
		
		$baseInfo['stime'] != 0 && $baseInfo['stime'] = date('Y-m-d H:i:s', $baseInfo['stime']);
		$baseInfo['etime'] != 0 && $baseInfo['etime'] = date('Y-m-d H:i:s', $baseInfo['etime']);
		
		return $baseInfo;
	}
	
	/**
	 * 发布调研
	 * @param int	$id		投票ID
	 */
	public static function publish($id)
	{
		$model = new Vote();
		// 读取问题是否为空
		$questionInfo = $model->getQuestionOne(['vote_id'=>$id], 'id');
		if(empty($questionInfo)) return Errcode::VOTE_QUE_UNSET; 
		
		// 读取功能设置是否为空
		/*$baseInfo = $model->getBaseOne(['id'=>$id], 'num');
		if(empty($baseInfo)) return Errcode::VOTE_SETUP_UNSET;*/
		
		// 读取发送对象
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'to_contact,abstract');
		if(empty($extraInfo))	return Errcode::VOTE_UNSET;
		
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
					'vote_id'	=> $id,
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
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/vote/'.$id.'/detail/aid/'.$aid,
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path'],
			'desc'		=> mb_substr($extraInfo['abstract'], 0, 50)
		];
		
		WxLogic::sendNewsMsgByCustom($sendMsg, $memids, 'survey');
		
		return Errcode::SUCCESS;
	}
	
	/**
	 * 删除
	 * @param int	$id		投票ID
	 */
	public static function del($id)
	{
		$model = new Vote();
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'is_publish,is_del');
		if(empty($baseInfo)) return Errcode::VOTE_UNSET;
		
		if($baseInfo['is_publish']){
			$model->updateMember(['vote_id'=>$id], ['is_del'=>1]);
		}
		$res = $model->updateBase(['id'=>$id], ['is_del'=>1]);
		
		return $res>0 ? true : false;
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
		in_array($status, [1,2,3]) && $where['status'] = $status;
		
		$model = new Vote();
		$count = $model->countBase($where);
		$list = $model->getBaseMorePage($where, 'id,userid,title,stime,etime,status,is_publish', 
			$offset, $psize, 'id DESC');
		
		$userids = [];
		foreach($list as $v){
			$userids[] = $v['userid'];
		}
		$userids = array_values(array_filter(array_unique($userids)));
		if(! empty($userids)){
			$userInfo = UserLogic::getNameByUserids($userids);
		}
		
		
		foreach($list as &$v){
			$v['title']			= htmlspecialchars_decode($v['title']);
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
	 * 预览
	 * @param int	$id		投票ID
	 */
	public static function preview($id)
	{
		$model = new Vote();
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'userid,imgid,title');
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'abstract');		
		// 查找图片地址
		$fileInfo = FileLogic::getOne($baseInfo['imgid'], false, true);
		$aid = AgentLogic::getAidByType('survey');
		
		$sendMsg[] = [
			'title'		=> htmlspecialchars_decode($baseInfo['title']),
			'desc'		=> mb_substr(htmlspecialchars_decode($extraInfo['abstract']), 0, 50, 'UTF-8'),
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/vote/'.$id.'/detail/aid/'.$aid.'/admin',
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path']
		];
		// 发送微信消息
		WxLogic::sendNewsMsgByAdmin($sendMsg, [$baseInfo['userid']], 'survey');
		return true;
	}
	
	/**
	 * 一个调研的所有回答
	 * @param int $id		投票ID
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 */
	public static function answerAll($id, $offset=0, $psize=10)
	{
		$model = new Vote();
		
		$count = $model->countMember(['vote_id'=>$id, 'is_answer'=>1, 'is_del'=>0]);
		$list = $model->getMemberMorePage(['vote_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 
			'id,create_time,memid', $offset, $psize, 'update_time DESC');
		
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
			$v['time']		= date('Y-m-d H:i:s', $v['create_time']);
			$v['name']		= isset($memInfo[$v['memid']]) ? $memInfo[$v['memid']]['name'] : '';
			$v['mobile']	= isset($memInfo[$v['memid']]) ? $memInfo[$v['memid']]['mobile'] : '';
			unset($v['create_time']);
		}
		
		return [empty($list) ? [] : $list, $count];
	}
	
	/**
	 * 统计
	 * @param int $id	投票ID
	 */
	public static function statis($id)
	{
		$model = new Vote();
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,stime,etime');
		
		// 读取所有问题
		$answer = [];
		$questionInfo = $model->getQuestionOne(['vote_id'=>$id, 'is_del'=>0],
			'id,type,option');
		$type = $questionInfo['type'];
		$option = explode(',', $questionInfo['option']);
		foreach($option as $v){
			$answer[] = ['title'=>$v, 'count'=>0];
		}
		unset($questionInfo);
		
		// 读取一个投票里面所有问题的所有答案
		$count = 0;
		$answerInfo = $model->getAnswerMore(['vote_id'=>$id, 'is_del'=>0], 'answer');
		
		foreach($answerInfo as $v){
			if($type == 1){
				$answer[$v['answer']-1]['count']++;
				$count++;
			}else{
				$_answer = explode(',', $v['answer']);
				foreach($_answer as $v1){
					$answer[$v1-1]['count']++;
					$count++;
				}
			}
		}
		
		foreach($answer as &$v){
			$v['rate'] = $count == 0? '0' : $v['rate'] = round(floatval($v['count']*100/$count), 1) .'%';
		}
		
		// 读取参与人数
		$joinCount = $model->countMember(['vote_id'=>$id, 'is_answer'=>1]);
		$data = [
			'answer' => $answer, 'title' => $baseInfo['title'],
			'people' => $joinCount, 'ticket' => $count,
			'stime'  => $baseInfo['stime'], 'etime' => $baseInfo['etime']
		];
		$baseInfo['stime'] !=0 && $data['stime'] = date('Y-m-d H:i:s', $data['stime']);
		$baseInfo['etime'] !=0 && $data['etime'] = date('Y-m-d H:i:s', $data['etime']);
		
		return $data;
	}

	/**
	 * 下载统计报表
	 * @param int $id	投票ID
	 */
	public static function downloadStatis($id)
	{
		$data = self::statis($id);
		
		$tableHead = [
			[
				[
					'title' => '选项',
					'width' => 30
				],
				[
					'title' => '投票',
					'width' => 10
				],
				[
					'title' => '占比',
					'width' => 10
				]
			]
		];
		$time = date('YmdHis', NOW_TIME);
		$title = '投票-'.$time;
		ExeclLogic::download($title, $tableHead, [$data['title']=>$data['answer']]);
	}

	/** 
	 * 一个人的回答
	 * @param int $id		回答记录ID
	 */
	public static function answerDetailByMem($id)
	{
		$model = new Vote();
		
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
				}
			}
		}
		
		return isset($questionInfo) ? $questionInfo : [];
	}
	
	/**
	 * 查找这条回答的上一个ID和下一个ID
	 * @param int $id		回答记录ID
	 * @param int $voteId 投票ID
	 */
	public static function selectAnswer($id, $voteId)
	{
		$model = new Vote();
		// 读取此时这条回答的回答时间
		$answerInfo = $model->getMemberOne(['id'=>$id], 'update_time');
		
		$where = ['vote_id'=>$voteId, 'is_del'=>0];
		// 读取下一条ID
		$info = $model->getAnswerOne(array_merge($where,['id'=>['gt', $id]]), 'id,memid');
		if(isset($info['id']) && ! empty($info['id'])){
			$nextInfo = [
				'id' => $info['id'],
				'memid' => $info['memid']
			];
		}
		
		// 读取上一条ID
		$info = $model->getAnswerOne(array_merge($where,['id'=>['lt', $id]]), 'id,memid');
		if(isset($info['id']) && ! empty($info['id'])){
			$preInfo = [
				'id' => $info['id'],
				'memid' => $info['memid']
			];
		}
		
		return [
			'nextInfo' => isset($nextInfo) ? $nextInfo : [], 
			'preInfo'  => isset($preInfo) ? $preInfo : []
		];
	}
	
}
