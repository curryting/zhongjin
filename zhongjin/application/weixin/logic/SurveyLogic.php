<?php
/**
 * 调研操作逻辑层
 * by sherlock
 */

namespace app\weixin\logic;

use think\Db;
use app\common\controller\Errcode;
use app\common\logic\FileLogic;
use app\common\logic\UserLogic;
use app\common\logic\FollowLogic;
use app\home\logic\WxLogic;
use app\common\model\Survey;
use app\home\logic\CustomerLogic;

define('APP_ID', 3);

class SurveyLogic{
	
	/**
	 * 读取全部
	 * @param int $offset	偏移量
	 * @param int $psize	一页大小
	 * @param int $memid	成员ID 0表示是管理员读取
	 */
	public static function lists($offset=0, $psize=10, $memid=0)
	{
		$surveyIds = $lists = [];
		$model = new Survey();
		if($memid != 0){
			// 成员读取
			$where = ['memid'=>$memid, 'is_del'=>0];
			$_list = $model->getMemberMorePage($where, 'survey_id,is_answer', $offset, $psize, 'id DESC');
			foreach($_list as $v){
				$surveyIds[] = $v['survey_id'];
				$v['is_answer'] == 1 && $answerInfo[$v['survey_id']] = true;
			}
			unset($_list);
			$surveyIds = array_values(array_filter(array_unique($surveyIds)));
			$where = ['is_publish'=>1, 'is_del'=>0, 'id'=> ['in', $surveyIds]];
			$lists = $model->getBaseMore($where,'id,create_time,title,imgid,stime,etime,userid', 'id DESC');
		}else{
			$where = ['is_publish'=>1, 'is_del'=>0];
			$lists = $model->getBaseMorePage($where, 'id,create_time,title,imgid,stime,etime,userid', $offset, $psize, 'id DESC');
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
			if($v['stime'] !=0 && $time < $v['stime']){
				$v['status'] = 0; // 未开始
			}else if($v['etime'] !=0 && $time > $v['etime']){
				$v['status'] = 2; // 已结束 
			}else{
				$v['status'] = 1; // 正在进行中
			}
			
			isset($answerInfo[$v['id']]) && $v['status'] = 3; // 已参与
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
		$model = new Survey();
		// 获取基本详情
		$baseInfo = $model->getBaseOne(['id'=>$id], 'create_time,update_time,userid,title,stime,etime,is_del,is_remind');
		if(empty($baseInfo)) throw new \Exception ('调研不存在', Errcode::SURVEY_UNSET);
		if($baseInfo['is_del']) throw new \Exception ('调研已删除', Errcode::SURVEY_DEL);
		
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
		
		// 判断调研状态
		$time = NOW_TIME;
		$status = 1;
		if($baseInfo['stime'] != 0 && $time < $baseInfo['stime']){
			$status = 0;
		}else if($baseInfo['etime'] !=0 && $time > $baseInfo['etime']){
			$status = 2;
		}
		
		// 获取作者名称
		$adminInfo = UserLogic::getNameByUserids([$baseInfo['userid']]);
		$author = empty($adminInfo[$baseInfo['userid']]) ? '' : $adminInfo[$baseInfo['userid']];
		unset($adminInfo);
		
		// 获取扩展信息
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'content,finish');
		$finish = $extraInfo['finish'];
		$detail =[
			'canRemind' => $canRemind,
			'author' => $author, 'etime' => intval($baseInfo['etime']),
			'title'		=> htmlspecialchars_decode($baseInfo['title']),
			'desc'		=> htmlspecialchars_decode($extraInfo['content']),
			'create_time'	=> date('Y-m-d H:i:s', $baseInfo['create_time'])
		];
		
		$baseInfo['etime'] > 0 && $detail['etime'] = date('Y-m-d H:i:s', $detail['etime']);
		unset($baseInfo);
		unset($extraInfo);
		
		// 查看问题详情
		$questionInfo = self::questionDetail($id);
		
		$data = ['detail'=>$detail];
		if(intval(ADMINID) >0){
			$data['detail']['status'] = $status;
			$data['join_mem_count']   = self::_countMember($id, 1, $model);
			$data['unjoin_mem_count'] = self::_countMember($id, 0, $model);
		}else{
			$answerInfo = $model->getMemberOne(['survey_id'=>$id, 'memid'=>MEMID, 'is_del'=>0], 'id,is_answer');
			$answerInfo['is_answer'] == 1 && $status = 3;
			$data['detail']['status'] = $status;
			if($status == 3){
				// 已参与 读取答案
				$answerContent = $model->getAnswerContentMore(['answer_id'=>$answerInfo['id'],'is_del'=>0], 'question_id,answer');
				foreach($answerContent as $v){
					$type = $questionInfo[$v['question_id']]['type'];
					switch($type){
						case 1:
							$k = $v['answer']-1;
							$tmp = $questionInfo[$v['question_id']]['option'];
							$tmp[$k]['checked'] = 1;
							$questionInfo[$v['question_id']]['option'] = $tmp;
							break;
						case 2:
							$tmp = $questionInfo[$v['question_id']]['option'];
							$_answer = json_decode($v['answer'], true);
							foreach($_answer as $v1){
								$k = $v1-1;
								$tmp[$k]['checked'] = 1;
							}
							$questionInfo[$v['question_id']]['option'] = $tmp;
							break;
						case 3:
						case 4:
							$questionInfo[$v['question_id']]['value'] = htmlspecialchars_decode($v['answer']);
							break;
					}
				}
			}else if($status == 2){
				$data['detail']['msg'] = $finish;
			}else if($status == 1){
				$data['detail']['msg'] = '调研尚未开始，敬请期待！';
			}
		}
		$data['question_info'] = array_values($questionInfo);
		unset($questionInfo);
		return $data;
	}
	
	/**
	 * 回答
	 * @param int $id		调研ID
	 * @param array $answer 回答的答案
	 * @param int $memid	成员ID 
	 * @param array $questionInfo	问题详情
	 */
	public static function answer($id, $answer, $memid, &$questionInfo)
	{
		$model = new Survey();
		
		// 读取调研成员表的ID
		$memInfo = $model->getMemberOne(['survey_id'=>$id, 'memid'=>$memid, 'is_del'=>0], 'id,is_answer');
		if(empty($memInfo)) return Errcode::SURVEY_UNSET;
		if($memInfo['is_answer'] == 1) return Errcode::SURVEY_ISSET_ANSWER;
		
		Db::startTrans();
		try{
			$model->updateMember(['id'=>$memInfo['id']], ['is_answer'=>1, 'update_time'=>NOW_TIME]);
			$data = [];
			foreach($answer as $k => $v){
				$tmp = [
					'answer_id'	=> $memInfo['id'],
					'question_id'	=> $k
				];
				if($questionInfo[$k]['type'] == 2){
					$tmp['answer'] = json_encode($v);
				}else{
					$tmp['answer'] = htmlspecialchars($v);
				}
				$data[] = $tmp;
			}
			$model->addAnswerContentMore($data);
			Db::commit();
		}catch(\Exception $e){
			Db::rollback();
			return false;
		}
		
		// 读取结束语
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'finish');
		return empty($extraInfo) ? '您已完成调研，谢谢您的参与！' : $extraInfo['finish'];
	}
	
	/**
	 * 一键提醒
	 * @param int $id		调研ID
	 */
	public static function remind($id)
	{
		$model = new Survey();
		// 读取基本信息
		$baseInfo = $model->getBaseOne(['id'=>$id], 'title,imgid');
		$extraInfo = $model->getExtraOne(['survey_id'=>$id], 'abstract');
		
		// 读取图片地址
		$fileInfo = FileLogic::getOne($baseInfo['imgid'], false, true, false);
		
		// 读取未进行调研的人
		$unAnswerMemids = self::_manageJoinMem($id, 0, $model);
		
		// 发送微信消息
		$sendMsg[] = [
			'title'		=> $baseInfo['title'],
			'url'		=> APP_HTTP.TLD.'.'.WEB_DOMAIN.'/weixin/index.html#/survey/'.$id.'/detail/aid/'.intval(session('agentid')),
			'picurl'	=> APP_HTTP.TLD.'.'.WEB_DOMAIN.$fileInfo['http_path'],
			'desc'		=> '请尽快完成调研'
		];
		
		empty($unAnswerMemids) || WxLogic::sendNewsMsgByCustom($sendMsg, $unAnswerMemids, 'survey');
		
		// 设置为已提醒状态
		$model->updateBase(['id'=>$id], ['is_remind'=>1, 'update_time'=>NOW_TIME]);
		return true;
	}
	
	/**
	 * 查看问题详情
	 * @param int $id	调研ID
	 */
	public static function questionDetail($id)
	{
		$model = new Survey();
		$_questionInfo = $model->getQuestionMore(['survey_id'=>$id, 'is_del'=>0], 'id,type,title,is_must,option,extra');
		$questionInfo = [];
		foreach($_questionInfo as $v){
			$v['title'] = htmlspecialchars_decode($v['title']);
			$v['required']  = $v['is_must'];
			$v['option'] = explode(',', $v['option']);
			$tmp = [];
			
			switch(intval($v['type'])){
				case 1:// 单选
				case 2:// 多选
					foreach($v['option'] as $v1){
						$tmp[] = ['title'=>$v1, 'checked'=>0];
					}
					
					if($v['type'] == 2){
						$extra = json_decode($v['extra'], true);
						$v['least'] = $extra['least'];
						$v['most'] = $extra['most'];
					}
					break;
				case 3:
				case 4:
					$v['value'] = '';
			}
			unset($v['is_must']);
			$questionInfo[$v['id']] = $v;
			$questionInfo[$v['id']]['option'] = $tmp;
		}
		unset($_questionInfo);
		return $questionInfo;
	}
	
	/**
	 * 读取参与和没参与调研的情况
	 * @param int $id	 调研ID
	 * @param int $type	 类型 1读取参与 0 读取未参与
	 * @param int $offset 偏移量
	 * @param int $psize 一页的大小
	 */
	public static function getJoinInfo($id, $type, $offset=0, $psize=10)
	{
		$model = new Survey();
		
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
					'name' => isset($memInfo[$v]) ? $memInfo[$v] : '',
					'avatar' => isset($followInfo[$v]) ? $followInfo[$v]['avatar'] : ''
				];
				$type == 1 && $info[$k]['time'] = isset($joinMemInfo[$v]) ? $joinMemInfo[$v] : '-';
			}
			unset($memInfo);
			unset($followInfo);
			$info = array_values($info);
		}
		
		return ['mem_info'=>$info];
	}
	
	/**
	 * 获取参与或者未参与人数
	 * @param int id		调研ID
	 * @param int $type		类型 0:未参与 1：参与
	 * @param type $model
	 */
	private static function _countMember($id, $type, $model)
	{
		return $model->countMember(['survey_id'=>$id, 'is_answer'=> $type, 'is_del'=>0]);
	} 

	/**
	 * 查看已经参与调研和还没有参与调研的人
	 * @param int $id	调研ID
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
			$_joinMemInfo = $model->getMemberMore(['survey_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 'memid,update_time');
		}else{
			$_joinMemInfo = $model->getMemberMorePage(['survey_id'=>$id, 'is_answer'=>1, 'is_del'=>0], 'memid,update_time', $offset, $psize);
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
					$unJoinInfo = $model->getMemberMore(['survey_id'=>$id, 'is_answer'=>0, 'is_del'=>0], 'memid');
				}else{
					$unJoinInfo = $model->getMemberMorePage(['survey_id'=>$id, 'is_answer'=>0, 'is_del'=>0], 'memid', $offset, $psize);
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
