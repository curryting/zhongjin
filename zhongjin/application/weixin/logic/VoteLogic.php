<?php
/**
 * 投票操作逻辑层
 * by sherlock
 */

namespace app\weixin\logic;

use think\Db;
use app\common\controller\Errcode;
use app\common\logic\FileLogic;
use app\common\logic\UserLogic;
use app\common\logic\FollowLogic;
use app\home\logic\WxLogic;
use app\common\model\Vote;
use app\home\logic\CustomerLogic;

define('APP_ID', 4);

class VoteLogic{
	
	/**
	 * 读取全部
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param int $memid	成员ID 0表示是管理员读取
	 */
	public static function lists($offset=0, $psize=10, $memid=0)
	{
		$voteIds = $lists = [];
		$model = new Vote();
		if($memid != 0){
			// 成员读取
			$where = ['memid'=>$memid, 'is_del'=>0];
			$_list = $model->getMemberMorePage($where, 'vote_id,is_answer', $offset, $psize, 'id DESC');
			foreach($_list as $v){
				$voteIds[] = $v['vote_id'];
				//$v['is_answer'] == 1 && $answerInfo[$v['vote_id']] = true;
			}
			unset($_list);
			$voteIds = array_values(array_filter(array_unique($voteIds)));
			
			// 读取投票列表
			$where = ['is_publish'=>1, 'is_del'=>0, 'id'=>['in', $voteIds]];
			$lists = $model->getBaseMore($where, 'id,create_time,title,imgid,stime,etime,userid,status,type,num', 'id DESC');
			$answerInfo = ['single_person'=>[], 'single_day'=>[]];
			foreach($lists as $v){
				if($v['type'] == 1){
					// 单人限投
					$answerInfo['single_person'][$v['id']]['num'] = $v['num'];
					$answerInfo['single_person'][$v['id']]['answer_count'] = 0;
				}else{
					// 单日限投
					$answerInfo['single_day'][$v['id']]['num'] = $v['num'];
					$answerInfo['single_day'][$v['id']]['answer_count'] = 0;
				}
			}
			$singlePersonIds = array_keys($answerInfo['single_person']);
			$singleDayIds = array_keys($answerInfo['single_day']);
			if(! empty($singlePersonIds)){
				// 读取单人限投的已投次数
				$info = $model->getAnswerMore(['memid'=>$memid, 'vote_id'=>['in', $singlePersonIds], 'is_del'=>0], 'vote_id');
				foreach($info as $v){
					$answerInfo['single_person'][$v['vote_id']]['answer_count']++;
				}
			}
			if(! empty($singleDayIds)){
				// 读取单人限投的已投次数
				$time = date('Y-m-d', NOW_TIME);
				$stime = strtotime($time.' 00:00:00');
				$etime = strtotime($time.' 23:59:59');
				$info = $model->getAnswerMore(['memid'=>$memid, 'vote_id'=>['in', $singleDayIds], 'is_del'=>0, 'create_time'=>['between', [$stime, $etime]]], 'vote_id');
				foreach($info as $v){
					$answerInfo['single_day'][$v['vote_id']]['answer_count']++;
				}
			}
			
			foreach($answerInfo as $v){
				foreach($v as $k1 => $v1){
					$v1['answer_count'] >= $v1['num'] && $complete[$k1] = true;
				}
			}
		}else{
			$where = ['is_publish'=>1, 'is_del'=>0];
			$lists = $model->getBaseMorePage($where, 'id,create_time,title,imgid,stime,etime,userid,status', $offset, $psize, 'id DESC');
		}
		
		$imgids = $userids = [];
		foreach($lists as $v){
			$imgids[] = $v['imgid'];
			$userids[] = $v['userid'];
		}
		
		// 查找图片地址
		$imgids = array_values(array_filter(array_unique($imgids)));
		if(! empty($imgids)){
			$imgInfo = FileLogic::getMore($imgids, false, true, false);
		}
		
		// 查找名字
		$userids = array_values(array_filter(array_unique($userids)));
		if(! empty($userids)){
			$userInfo = UserLogic::getNameByUserids($userids);
		}
		
		foreach($lists as &$v){
			$v['time'] = date('Y-m-d', $v['create_time']);
			$v['cover'] = isset($imgInfo[$v['imgid']]) ? $imgInfo[$v['imgid']]['http_path'] : '';
			$v['author'] = isset($userInfo[$v['userid']]) ? $userInfo[$v['userid']] : '';
			
			$time = NOW_TIME;
			if($v['status'] == 1){
				if($v['stime'] !=0 && $time < $v['stime']){
					$v['status'] = 0; // 未开始
				}else if($v['etime'] !=0 && $time > $v['etime']){
					$v['status'] = 2; // 已结束 
				}
			}
			
			isset($complete[$v['id']]) && $v['status'] = 4; // 已投票
			unset($v['stime']);
			unset($v['etime']);
			unset($v['imgid']);
			unset($v['create_time']);
		}
		
		return $lists;
	}
	
	/**
	 * 详情
	 * @param int $id		调研ID
	 */
	public static function detail($id)
	{
		$model = new Vote();
		// 获取基本详情
		$baseInfo = $model->getBaseOne(['id'=>$id], 'create_time,update_time,userid,title,stime,etime,is_del,is_remind,type,num,status');
		if(empty($baseInfo)) throw new \Exception ('调研不存在', Errcode::VOTE_UNSET);
		if($baseInfo['is_del']) throw new \Exception ('投票已删除', Errcode::VOTE_DEL);
		
		// 判断投票状态
		$time = NOW_TIME;
		$status = $baseInfo['status'];
		if($status == 1){
			if($baseInfo['stime'] != 0 && $time < $baseInfo['stime']){
				$status = 0;
			}else if($baseInfo['etime'] !=0 && $time > $baseInfo['etime']){
				$status = 2;
			}
		}
		
		// 获取作者名称
		$adminInfo = UserLogic::getNameByUserids([$baseInfo['userid']]);
		$author = empty($adminInfo[$baseInfo['userid']]) ? '' : $adminInfo[$baseInfo['userid']];
		unset($adminInfo);
		
		// 获取扩展信息
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'content,finish');
		$finish = $extraInfo['finish'];
		$detail =[
			'author' => $author, 'etime' => intval($baseInfo['etime']),
			'type' => $baseInfo['type'], 'num' => $baseInfo['num'],
			'title'		=> htmlspecialchars_decode($baseInfo['title']),
			'desc'		=> htmlspecialchars_decode($extraInfo['content']),
			'create_time'	=> date('Y-m-d H:i:s', $baseInfo['create_time'])
		];
		
		$baseInfo['etime'] > 0 && $detail['etime'] = date('Y-m-d H:i:s', $detail['etime']);
		unset($extraInfo);
		
		$data = ['detail'=>$detail];
		if(intval(ADMINID) >0){
			// 更新提醒状态
			$canRemind = 1;
			if($baseInfo['is_remind'] == 1){
				// 已经提醒过 并且时间是否已经过去半个小时
				if(NOW_TIME - $baseInfo['update_time'] >60*30){
					$model->updateBase(['id'=>$id], ['is_remind'=>0]);
				}else{
					$canRemind = 0;
				}
			}
			$data['detail']['canRemind'] = $canRemind;
			$data['detail']['status'] = $status;
			$data['join_mem_count']   = self::_countMember($id, 1, $model);
			$data['unjoin_mem_count'] = self::_countMember($id, 0, $model);
		}else{
			// 查看问题详情
			$questionInfo = self::questionDetail($id);
			$data['detail']['status'] = $status;
			
			$answerInfo = $model->getAnswerOne(['vote_id'=>$id, 'memid'=>MEMID, 'is_del'=>0], 'id,answer', 'id DESC');
			empty($answerInfo) || $status = 4;  // 已投票
			if($status == 4){
				$answer = $answerInfo['answer'];
				$tmp = '';
				if($questionInfo['type'] == 1){
					$tmp = $questionInfo['option'];
					$tmp[$answer-1]['checked'] = 1;
				}else{
					$answer = explode(',', $answer);
					$tmp = $questionInfo['option'];
					foreach($answer as $v){
						$tmp[$v-1]['checked'] = 1;
					}
				}
				$questionInfo['option'] = $tmp;
				// 判断已经回答了多少次
				if($baseInfo['type'] == 2){
					// 单日限投
					$time = date('Y-m-d', NOW_TIME);
					$stime = strtotime($time.' 00:00:00');
					$etime = strtotime($time.' 23:59:59');
					$count = $model->countAnswer(['vote_id'=>$id,'memid'=>MEMID,'create_time'=>['between', [$stime, $etime]]]);
					if($count >=$baseInfo['num']){
						$data['detail']['msg'] = '本投票每日只能投'.$baseInfo['num'].'次';
						$data['detail']['status'] = 4; // 已投票
					}
				}else{
					$count = $model->countAnswer(['vote_id'=>$id,'memid'=>MEMID]);
					if($count >=$baseInfo['num']){
						$data['detail']['msg'] = '本投票一共只能投'.$baseInfo['num'].'次';
						$data['detail']['status'] = 4; // 已投票
					}
				}
			}else if($status == 2){
				// 结束
				$data['detail']['msg'] = $finish;
			}else if($status == 0){
				$data['detail']['msg'] = '投票尚未开始，敬请期待';
			}
			
			$data['question_info'] = $questionInfo;
		}

		unset($questionInfo);
		return $data;
	}
	
	/**
	 * 统计
	 * @param int $id		投票ID
	 */
	public static function statis($id)
	{
		// 查看问题详情
		$data = [];
		$questionInfo = self::questionDetail($id);
		$type = $questionInfo['type'];
		foreach($questionInfo['option'] as $v){
			$data[] = ['title'=>$v['title'], 'count'=>0];
		}
		unset($questionInfo);
		
		// 读取所有答案
		$model = new Vote();
		$count = 0;
		$answerInfo = $model->getAnswerMore(['vote_id'=>$id, 'is_del'=>0], 'answer');
		
		foreach($answerInfo as $v){			
			if($type == 1){
				$data[$v['answer']-1]['count']++;
				$count++;
			}else{
				$_answer = explode(',', $v['answer']);
				foreach($_answer as $v1){
					$data[$v1-1]['count']++;
					$count++;
				}
			}
		}
		
		foreach($data as &$v){
			$v['rate'] = $count == 0? '0' : $v['rate'] = round(floatval($v['count']*100/$count), 1);
		}
		
		return [$data, $type];
	}
	
	/**
	 * 回答
	 * @param int $id		投票ID
	 * @param array $answer 回答的答案
	 * @param int $memid	成员ID 
	 * @param array $questionInfo	问题详情
	 */
	public static function answer($id, $answer, $memid, &$questionInfo)
	{
		$model = new Vote();
		
		$baseInfo = $model->getBaseOne(['id'=>$id], 'type,num,stime,etime,status,is_publish');
		if(empty($baseInfo['is_publish'])) return Errcode::VOTE_PUBLISH;
		
		$time = NOW_TIME;
		if($baseInfo['status'] == 1){
			if($baseInfo['stime'] != 0 && $time < $baseInfo['stime']){
				return Errcode::VOTE_NO_BEGINNING;
			}else if($baseInfo['etime'] !=0 && $time > $baseInfo['etime']){
				return Errcode::VOTE_OVER;
			}
		}else if($baseInfo['status'] == 2){
			return Errcode::VOTE_OVER;
		}else if($baseInfo['status'] == 3){
			return Errcode::VOTE_SUSPEND;
		}
		
		// 查看是否有权限投票
		$memInfo = $model->getMemberOne(['vote_id'=>$id, 'memid'=>$memid], 'id');
		if(empty($memInfo)) return Errcode::NO_AUTH;
			
		if($baseInfo['type'] == 2){
			// 单日限投
			$time = date('Y-m-d', NOW_TIME);
			$stime = strtotime($time.' 00:00:00');
			$etime = strtotime($time.' 23:59:59');
			$where = ['vote_id'=>$id,'memid'=>$memid,'create_time'=>['between', [$stime, $etime]]];
			$count = $model->countAnswer($where);
		}else{
			// 单人限头
			$count = $model->countAnswer(['vote_id'=>$id,'memid'=>$memid]);
		}
		if($count >= $baseInfo['num']) return Errcode::VOTE_UPPER_LIMIT;

		Db::startTrans();
		try{
			$model->updateMember(['vote_id'=>$id,'memid'=>$memid], ['is_answer'=>1, 'update_time'=>NOW_TIME]);
			$model->addAnswerOne(['create_time'=>NOW_TIME, 'vote_id'=>$id, 'memid'=>$memid, 'answer'=>$answer]);
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			return Errcode::INSERT_ERR;
		}
		
		// 读取结束语
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'finish');
		return empty($extraInfo) ? '您已完成投票，谢谢您的参与！' : $extraInfo['finish'];
	}
	
	/**
	 * 一键提醒
	 * @param int $id		投票ID
	 */
	public static function remind($id)
	{
		$model = new Vote();
		// 读取基本信息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,imgid');
		$extraInfo = $model->getExtraOne(['vote_id'=>$id], 'abstract');
		
		// 读取图片地址
		$fileInfo = FileLogic::getOne($baseInfo['imgid'], false, true, false);
		
		// 读取未进行投票的人
		$unAnswerMemids = self::_manageJoinMem($id, 0, $model);
		
		// 发送微信消息
		$sendMsg[] = [
			'title'		=> $baseInfo['title'],
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/vote/'.$id.'/detail/aid/'.intval(session('agentid')),
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path'],
			'desc'		=> '请尽快完成投票'
		];
		
		empty($unAnswerMemids) || WxLogic::sendNewsMsgByCustom($sendMsg, $unAnswerMemids, 'survey');
		
		// 设置为已提醒状态
		$model->updateBase(['id'=>$id], ['is_remind'=>1, 'update_time'=>NOW_TIME]);
		return true;
	}
	
	/**
	 * 查看问题详情
	 * @param int $id	投票ID
	 */
	public static function questionDetail($id)
	{
		$model = new Vote();
		$info = $model->getQuestionOne(['vote_id'=>$id, 'is_del'=>0], 'id,type,option,extra');
		
		$info['option'] = explode(',', $info['option']);
		$tmp = [];
		foreach($info['option'] as $v){
			$tmp[] = ['title'=>$v, 'checked'=>0];
		}
		$info['option'] = $tmp;
		
		return $info;
	}
	
	/**
	 * 读取参与和没参与投票的情况
	 * @param int $id	 投票ID
	 * @param int $type	 类型 1读取参与 0 读取未参与
	 * @param int $offset 偏移量
	 * @param int $psize 一页的大小
	 */
	public static function getJoinInfo($id, $type, $offset=0, $psize=10)
	{
		$model = new Vote();
		
		// 查看已参与和未参与的成员
		if($type == 1){
			// 已参与
			list($ids,$joinMemInfo) = self::_manageJoinMem($id, 1, $model, $offset, $psize);
		}else{
			// 未参与
			$ids = self::_manageJoinMem($id, 0, $model, $offset, $psize);
		}

		// 读取人名
		$info = [];
		if(! empty($ids)){
			$_memInfo = CustomerLogic::getInfoByMemids($ids, true, false);
			foreach($_memInfo as $v){
				$memInfo[$v['id']] = $v['name'];
			}
			unset($_memInfo);
			
			// 读取图像信息
			$followInfo = FollowLogic::getInfoByMemids($ids);
			
			foreach($ids as $k => $v){
				$info[$k] = [
					'id'	=> $v,
					'name'	=> isset($memInfo[$v]) ? $memInfo[$v] : '',
					'avatar' => isset($followInfo[$v]) ? $followInfo[$v]['avatar'] : ''
				];
				$type == 1 && $info[$k]['time'] = isset($joinMemInfo[$v]) ? $joinMemInfo[$v] : '-';
			}
			unset($memInfo);
			unset($followInfo);
			$info = array_values($info);
		}
		
		return ['mem_info'=>$info, 'ids'=>$ids];
	}
	
	/**
	 * 读取成员答案
	 * @param int $id 投票ID
	 * @param array $memids 客户memids
	 */
	public static function answerByIds($id, $memids)
	{
		if(empty($memids)) return [];
		
		$model = new Vote();
		$data = [];
		$info = $model->getAnswerMore(['vote_id'=>$id, 'memid'=>['in', $memids]], 'answer,memid');
		foreach($info as $v){
			$data[$v['memid']] = $v['answer'];
		}
		return $data;
	}

	/**
	 * 获取参与或者未参与人数
	 * @param int id		投票ID
	 * @param int $type		类型 0:未参与 1：参与
	 * @param type $model
	 */
	private static function _countMember($id, $type, $model)
	{
		return $model->countMember(['vote_id'=>$id, 'is_answer'=> $type, 'is_del'=>0]);
	} 

	/**
	 * 查看已经参与调研和还没有参与投票的人
	 * @param int $id	投票ID
	 * @param int $type 0:没参与 1：已参与 2返回没参与的和已参与的
	 * @param resrouce $model	操作对象
	 * @param int $offset 偏移量
	 * @param int $psize 一页的大小
	 */
	private static function _manageJoinMem($id, $type, $model, $offset=-1, $psize=10)
	{
		// 获取已经参与的成员
		$joinMemids = $joinMemInfo = [];
		if($offset <0){
			$_joinMemInfo = $model->getMemberMore(['vote_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 'memid,update_time');
		}else{
			$_joinMemInfo = $model->getMemberMorePage(['vote_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 'memid,update_time', $offset, $psize);
		}
		
		foreach($_joinMemInfo as $v){
			$joinMemids[] = $v['memid'];
			$joinMemInfo[$v['memid']] = date('Y-m-d H:i:s', $v['update_time']);
		}
		unset($_joinMemInfo);
		
		$info = [];
		switch($type){
			case 0:
			case 2:
				$unJoinMemids = [];
				if($offset <0){
					$unJoinInfo = $model->getMemberMore(['vote_id'=>$id, 'is_answer'=>0, 'is_del'=>0], 'memid');
				}else{
					$unJoinInfo = $model->getMemberMorePage(['vote_id'=>$id, 'is_answer'=>0, 'is_del'=>0], 'memid', $offset, $psize);
				}
				
				foreach($unJoinInfo as $v){
					$unJoinMemids[] = $v['memid'];
				}
				unset($unJoinInfo);
				$type == 0 && $info = $unJoinMemids;
				$type == 2 && $info = [$joinMemids, $unJoinMemids];
				break;
			case 1:
				$info = [$joinMemids, $joinMemInfo];
				break;
		}
		return $info;
	}
}
